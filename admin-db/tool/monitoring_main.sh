#!/bin/bash

script_dir=$(realpath "$0")
script_dir=$(dirname "$script_dir")

# database connection
sql_client='psql'

db_table_servers='monitoring.servers'
db_table_scripts='monitoring.scripts'
db_table_targets='monitoring.targets'
db_table_types='monitoring.types'
db_table_log='monitoring.log'
db_table_series='monitoring.series'

# parameters
delimeter=$'\t'
new_line=$'\n'

value="$1${delimeter}"
length=0
while [ "${#value}" != "$length" ]
do
	length=${#value}
	value="${value//${delimeter}${delimeter}/${delimeter}}"
done
while [ -n "${value%%${delimeter}*}" ]
do
	export ${value%%${delimeter}*}
	value=${value#*${delimeter}}
done

# check configuration
if [ -z "$ADMIN_DB_HOST" ] || [ -z "$ADMIN_DB_PORT" ] || [ -z "$ADMIN_DB_NAME" ] || [ -z "$ADMIN_DB_USER" ] || [ -z "$ADMIN_DB_PSW" ]
then
	logger -s -i "MONITORING:Database connection parameters not found"
	env | logger -s -i
	exit 222
fi

export PGPASSWORD="$ADMIN_DB_PSW"

sql_cmd="$sql_client -h $ADMIN_DB_HOST -p $ADMIN_DB_PORT -d $ADMIN_DB_NAME -U $ADMIN_DB_USER -t -c"

#env

# ------------------------------
# Server stage
# ------------------------------

if [ -z "$server_id" ] && [ -z "$script_id" ]
then
	echo === STAGE 1 ===
	
	echo --- Clean up log ---
	
	sql="DELETE FROM $db_table_log WHERE time < NOW() - INTERVAL '3 days'"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	echo --- Clean up series ---
	
	sql="DELETE FROM $db_table_series WHERE time < NOW() - INTERVAL '7 days'"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	echo --- Get server id of $(hostname) ---
	
	sql="SELECT id, run_count FROM $db_table_servers WHERE name = '$(hostname)' AND enabled"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ -z "$sql" ]; then exit $code; fi
	
	server_id="${sql%% *}"
	run_count="${sql##* }"
	
	let "run_count = ($run_count + 1) % 3628800"
	
	echo --- Update run count ---
	
	sql="UPDATE $db_table_servers SET run_count = $run_count WHERE id = $server_id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	echo --- Start scripts of server $server_id ---
	
	param="server_id=$server_id${delimeter}run_count=$run_count"
	param="$param${delimeter}ADMIN_DB_HOST=$ADMIN_DB_HOST"
	param="$param${delimeter}ADMIN_DB_PORT=$ADMIN_DB_PORT"
	param="$param${delimeter}ADMIN_DB_NAME=$ADMIN_DB_NAME"
	param="$param${delimeter}ADMIN_DB_USER=$ADMIN_DB_USER"
	param="$param${delimeter}ADMIN_DB_PSW=$ADMIN_DB_PSW"
	param="$param${delimeter}MONITORING_USER=$MONITORING_USER"
	
	. "$0" "$param"
	
	exit
fi


# ------------------------------
# Scripts stage
# ------------------------------

if [ -n "$server_id" ] && [ -n "$run_count" ] && [ -z "$script_id" ]
then
	echo === STAGE 2 ===
	
	echo --- Get script ids ---
	
	sql="SELECT id FROM $db_table_scripts WHERE server_id = $server_id AND enabled"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	sql="${sql#"${sql%%[![:space:]]*}"}"
	
	for id in $sql
	do
		echo --- Start targets of script $id ---
	
		. "$0" "script_id=$id${delimeter}$1"
	done
	
	return
fi


# ------------------------------
# Targets stage
# ------------------------------

if [ -n "$run_count" ] && [ -n "$script_id" ] && [ -z "$target_ids" ]
then
	echo === STAGE 3 ===
	
	echo --- Get target ids ---
	
	sql="SELECT id, period FROM $db_table_targets WHERE script_id = $script_id AND enabled"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	sql="${sql#"${sql%%[![:space:]]*}"}"
	sql="${sql// |/}"
	
	targets=""
	id=""
	
	echo --- Period filter ---
	
	for period in $sql
	do
		if [ -z "$id" ]
		then
			id=$period
		else
			let "period = $run_count % $period"
			
			if [ $period == 0 ]
			then
				targets="$targets $id"
			fi
			
			id=""
		fi
	done
	
	if [ -n "$targets" ]
	then
		target_ids="${targets#"${targets%%[![:space:]]*}"}"
	
		. "$0" "target_ids=$target_ids${delimeter}$1" &
		
		echo --- Started script $script_id on targets $target_ids PID $! ---
	fi
	
	return
fi


# ------------------------------
# Script stage
# ------------------------------

if [ -n "$script_id" ] && [ -n "$target_ids" ] && [ -z "$target_id" ]
then
	echo === STAGE 4 ===	
	
	temp_dir=$(mktemp -d)
	if [ ! -d "$temp_dir" ]
	then
		logger -s -i "MONITORING:Can not create temp directory"
		exit 201
	fi
	
	echo --- Write script file ---
	
	script_path="$temp_dir/script"
	
	sql="SELECT script FROM $db_table_scripts WHERE id = $script_id"
	echo $sql
	$sql_cmd "$sql" | sed -e 's/^\s//' -e 's/\s*+$//' > "$script_path"
	
	code=0
	
	if [ ! -r "$script_path" ]
	then
		logger -s -i "MONITORING:Can not write script file"
		code=202
	fi
	
	if [ $code == 0 ]
	then
		echo --- Write script runner ---
	
		runner_path="$script_path.sh"
		
		echo '#!/bin/bash
		' > "$runner_path"
		
		IFS= read -r line < "$script_path"
		
		if [[ "$line" =~ ^#!\/.+\/bash\s*$ ]]
		then
			echo --- Bash script $script_id ---
			
			echo bash '"'$script_path'"' '"$1"' >> "$runner_path"
		elif [[ "$line" =~ ^#!\/\S+\s+python.*$ ]]
		then
			echo --- Python script $script_id ---
			
			echo python '"'$script_path'"' '"$1"' >> "$runner_path"
		elif [[ "$line" =~ ^\<\?php\s*$ ]]
		then
			echo --- PHP script $script_id ---
			
			echo php '"'$script_path'"' '"$1"' >> "$runner_path"
		else
			logger -s -i "MONITORING:Unknown script format $script_id"
			cat "$script_path" | logger -s -i
			code=203
		fi
		
		echo exit '$?' >> "$runner_path"
	fi
	
	if [ $code == 0 ]
	then
		for id in $target_ids
		do	
			echo --- Start target $id ---
			
			. "$0" "target_id=$id${delimeter}temp_dir=$temp_dir${delimeter}runner_path=$runner_path${delimeter}$1"
		done
	fi
	
	if [ -d "$temp_dir" ]
	then
		echo --- Remove temp directory ---
		
		rm -r "$temp_dir"
	fi
	
	exit $code
fi


# ------------------------------
# Target stage
# ------------------------------

if [ -n "$script_id" ] && [ -n "$target_id" ] && [ -n "$runner_path" ]
then
	echo === STAGE 5 ===
	
	echo --- Write data file ---
	
	sql="SELECT script_data FROM $db_table_targets WHERE id = $target_id AND script_data IS NOT NULL"
	echo $sql
	$sql_cmd "$sql" | sed -e 's/^\s//' -e 's/\s*+$//' > "$temp_dir/data"
	#cat "$temp_dir/data"
	
	echo --- Out path ---
	
	out_path="$runner_path.$target_id.out"
	touch "$out_path"
	
	echo --- Target $target_id ---
	
	sql="SELECT target FROM $db_table_targets WHERE id = $target_id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ -z "$sql" ]; then exit $code; fi
	
	
	if [ -n "$MONITORING_USER" ]
	then
		echo --- Exec by user $MONITORING_USER ---
	
		sudo -u "$MONITORING_USER" bash "$runner_path" "$sql" 2>&1 > "$out_path"
		script_code=$?
	else
		echo --- Exec by user $(whoami) ---
	
		bash "$runner_path" "$sql" 2>&1 > "$out_path"
		script_code=$?
	fi
	
	echo --- Write log ---
		
	sql="INSERT INTO $db_table_log (target_id, code, output) VALUES ($target_id, $script_code, '$(cat "$out_path")')"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	echo --- Script code $script_code ---
	
	if [ $script_code != 0 ]
	then
		return
	fi
	
	echo --- Check metrics ---
	
	mapfile -t metrics < <( sed -n -E '/METRIC#.+#METRIC/p' "$out_path" | sed -e 's/.*METRIC#//' -e 's/#METRIC.*/#/' )
	
	if [ ${#metrics[*]} == 0 ]
	then
		echo --- No metric found ---
		
		return
	fi
			
	echo --- Get text id ---
	
	sql="SELECT text_id, name FROM $db_table_scripts WHERE id = $script_id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ -z "$sql" ]; then exit $code; fi
	
	text_id="${sql%% *}"
	target_name="${sql##* | }"
	
	sql="SELECT text_id, name FROM $db_table_targets WHERE id = $target_id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ -z "$sql" ]; then exit $code; fi
	
	text_id="$text_id@${sql%% *}"
	short_name="${sql##* | }"
	target_name="$target_name [$short_name]"
	
	echo --- Parse metrics ---	
	
	for line in "${metrics[@]}"
	do
		echo --- Metric string $line ---
		
		value=${line%%\#*}
		line=${line#*\#}
		type_id=${line%%\#*}
		line=${line#*\#}
		object=${line%%\#*}
		line=${line#*\#}
		description=${line%%\#*}
		
		value="${value//[[:space:]]/}"
		
		type_id="${type_id//@/}"
		type_id="${type_id//[[:space:]]/}"
		
		object="${object//@/|}"
		object="${object#"${object%%[![:space:]]*}"}"
		object="${object%"${object##*[![:space:]]}"}"
		
		description="${description//|/$new_line}"
		description="${description#"${description%%[![:space:]]*}"}"
		description="${description%"${description##*[![:space:]]}"}"
		
		echo $value - metric value
		echo $type_id - type text id
		echo $object - metric object name, optional
		echo $description - metric description, optional
		
		if [ -n "$value" ] && [ -n "$type_id" ]
		then
			echo --- Get type $type_id ---
			
			sql="SELECT is_alert, name, description FROM $db_table_types WHERE text_id = '$type_id'"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			sql="${sql#"${sql%%[![:space:]]*}"}"
			
			echo $sql
			
			if [ -n "$sql" ]
			then
				is_alert="${sql%% *}"
				is_alert="${is_alert/f/FALSE}"
				is_alert="${is_alert/t/TRUE}"
				sql="${sql#* | }"
				type_name="${sql%% |*}"
				sql="${sql#* |}"
				type_description="$(echo $sql | sed 's/\s*+\s|\s|\s/\n/g')"
			else
				is_alert=TRUE
				type_name="UNKNOWN TYPE"
				type_description="$type_id $object"
				type_description="${type_description%"${type_description##*[![:space:]]}"}"
			fi
		
			echo $is_alert - is_alert
			echo $type_name - type name
			echo $type_description - type description
						
			series_name="$target_name $type_name"
			series_short_name="$short_name"
			object_id=""
			
			if [ -n "$object" ]
			then
				object_id="@${object//@/-}"
				object_id="${object_id//[[:space:]]/-}"
				
				series_name="$series_name [$object]"
				series_short_name="$series_short_name $object"
			fi
			
			series_text_id="$text_id@$type_id$object_id"
			series_text_id="${series_text_id,,}"
			
			series_description=""
			if [ -n "$type_description" ]
			then
				series_description="$type_description"
			fi			
			if [ -n "$description" ]
			then
				if [ -n "$series_description" ]
				then
					series_description="$series_description"$'\n'"$description"
				else
					series_description="$description"
				fi
			fi
			if [ -n "$series_description" ]
			then
				series_description="'$series_description'"
			else
				series_description=NULL
			fi
			
			echo --- Add series row of $series_text_id ---
				
			sql="INSERT INTO $db_table_series (target_id, text_id, is_alert, value, name, short_name, description) VALUES ($target_id, '$series_text_id', $is_alert, '$value', '$series_name', '$series_short_name', $series_description)"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			if [ $code != 0 ]; then exit $code; fi
		fi
	done
		
	return
fi

echo === UNEXPECTED BLOCK - WILL NEVER EXECUTE ===

env

exit 200
