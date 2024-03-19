#!/bin/bash

script_dir=$(realpath "$0")
script_dir=$(dirname "$script_dir")

# database connection
sql_client='/usr/bin/psql'
db_host='10.10.4.185'
db_port='5432'
db_user=''
db_password=''
db_name='admindb'
db_table_servers='monitoring.servers'
db_table_scripts='monitoring.scripts'
db_table_targets='monitoring.targets'
db_table_alerts='monitoring.alerts'
db_table_log='monitoring.log'
db_table_series='monitoring.series'

export PGPASSWORD="$db_password"

sql_cmd="$sql_client -h $db_host -U $db_user -p $db_port -d $db_name -t -c"


# ------------------------------
# Server stage
# ------------------------------

if [ "$server_id" == "" ]
then
	echo === STAGE 1 ===
	
	echo --- Clean up log ---
	
	sql="DELETE FROM $db_table_log WHERE time < NOW() - INTERVAL '7 days'"
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	echo --- Clean up series ---
	
	sql="DELETE FROM $db_table_series WHERE time < NOW() - INTERVAL '7 days'"
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	echo --- Get server id ---
	
	sql="SELECT id, run_count FROM $db_table_servers WHERE name = '$(hostname)'"
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ "$sql" == "" ]; then exit $code; fi
	
	export server_id="${sql%% *}"
	run_count="${sql##* }"
	
	let "run_count = ($run_count + 1) % 3628800"
	
	echo --- Update run count ---
	
	sql="UPDATE $db_table_servers SET run_count = $run_count WHERE id = $server_id"
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	export run_count=$run_count
	
	echo --- Start stage 2 of server $server_id ---
	
	. "$0" &
	
	exit
fi


# ------------------------------
# Scripts stage
# ------------------------------

if [ "$server_id" != "" ] && [ "$run_count" != "" ] && [ "$script_id" == "" ]
then
	echo === STAGE 2 ===
	
	echo --- Get script ids ---
	
	sql="SELECT id FROM $db_table_scripts WHERE server_id = $server_id"
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ "$sql" == "" ]; then exit $code; fi
	
	for id in $sql
	do
		echo --- Start script $id ---
	
		export script_id=$id
		. "$0" &
	done
	
	exit
fi


# ------------------------------
# Targets stage
# ------------------------------

if [ "$run_count" != "" ] && [ "$script_id" != "" ] && [ "$target_ids" == "" ]
then
	echo === STAGE 3 ===
	
	echo --- Get target ids ---
	
	sql="SELECT id, period FROM $db_table_targets WHERE script_id = $script_id"
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	sql="${sql// |/}"
	if [ "$sql" == "" ]; then exit $code; fi
	
	targets=""
	id=""
	
	for period in $sql
	do
		if [ "$id" == "" ]
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
	
	if [ "$targets" == "" ]; then exit; fi
	
	export target_ids="${targets#"${targets%%[![:space:]]*}"}"
	. "$0" &
	
	exit
fi


# ------------------------------
# Script stage
# ------------------------------

if [ "$script_id" != "" ] && [ "$target_ids" != "" ] && [ "$target_id" == "" ]
then
	echo === STAGE 4 ===	
	
	temp=$(mktemp -d)
	if [ ! -d "$temp" ]
	then
		echo Temp directory creation error
		exit 201
	fi
	
	export script_path="$temp/script"
	
	sql="SELECT script FROM $db_table_scripts WHERE id = $script_id"
	$sql_cmd "$sql" | sed -e 's/^\s//' -e 's/\s*+$//' > "$script_path"
	
	code=0
	
	if [ ! -r "$script_path" ]
	then
		echo Temp script creation error
		code=202
	fi
	
	if [ $code == 0 ]
	then
		runner_path="$script_path.sh"
		
		echo '#!/bin/bash
		' > "$runner_path"
		
		IFS= read -r line < "$script_path"
		
		if [ -n "$(echo $line | sed -E -n '/^#!\/.+\/bash\s*$/p')" ]
		then
			echo --- Bash script $script_id ---
			
			echo bash '"'$script_path'"' '"$1"' >> "$runner_path"	
		elif [ -n "$(echo $line | sed -E -n '/^<\?php\s*$/p')" ]
		then
			echo --- PHP script $script_id ---
			
			echo php '"'$script_path'"' '"$1"' >> "$runner_path"
		else
			echo Script unknown format error
			code=203
		fi
		
		echo exit '$?' >> "$runner_path"
	fi
	
	if [ $code == 0 ]
	then		
		#cat "$runner_path"
		
		export runner_path="$runner_path"
		
		for id in $target_ids
		do
			export target_id=$id
			. "$0"
		done
	fi
	
	if [ -d "$temp" ]
	then
		rm -r "$temp"
	fi
	
	exit $code
fi


# ------------------------------
# Target stage
# ------------------------------

if [ "$script_id" != "" ] && [ "$target_id" != "" ] && [ "$runner_path" != "" ]
then
	echo === STAGE 5 ===
	
	echo --- Target $target_id ---
	
	sql="SELECT target FROM $db_table_targets WHERE id = $target_id"
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ "$sql" == "" ]; then exit $code; fi
	
	echo --- Exec ---
	
	out_path="$runner_path.$target_id.out"
	touch "$out_path"
	
	bash "$runner_path" "$sql" 2>&1 > "$out_path"
	script_code=$?
	script_out=$(cat "$out_path")
	
	echo --- Write log ---
		
	sql="INSERT INTO $db_table_log (target_id, code, output) VALUES ($target_id, $code, '$(cat "$out_path")')"
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	echo --- Get text id ---
	
	sql="SELECT text_id FROM $db_table_scripts WHERE id = $script_id"
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ "$sql" == "" ]; then exit $code; fi
	
	text_id="$sql"
	
	sql="SELECT text_id FROM $db_table_targets WHERE id = $target_id"
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ "$sql" == "" ]; then exit $code; fi
	
	text_id="'$text_id@$sql'"
	
	echo --- Get metric ---
	
	metric="NULL"
	
	if [ -n "$(echo $script_out | sed -E -n '/METRIC#.+#METRIC/p')" ]
	then
		metric=$(echo $script_out | sed -e 's/^.*METRIC#//' -e 's/#METRIC.*$//')
	fi
	
	echo --- Get alert ---
	
	is_alert="FALSE"
	alert_name="NULL"
	alert_description="NULL"
	
	if [ $script_code != 0 ]
	then
		is_alert="TRUE"
		alert_name="'UNKNOWN ERROR $script_code'"
	
		sql="SELECT name, description FROM $db_table_alerts WHERE script_id = $script_id AND code = $script_code"
		sql=$($sql_cmd "$sql")
		code=$?
		sql="${sql#"${sql%%[![:space:]]*}"}"
		
		if [ "$sql" != "" ]; then
			alert_name="'$(echo $sql | sed -e 's/ |.*$//')'"
			alert_description="'$(echo $sql | sed -e 's/^[^\|]*|\s*//' -e 's/\s*+\s|\s/\n/g')'"
		fi
	fi
	
	echo --- Add series row ---
		
	sql="INSERT INTO $db_table_series (target_id, text_id, metric, is_alert, alert_name, alert_description) VALUES ($target_id, $text_id, $metric, $is_alert, $alert_name, $alert_description)"
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	
	return
fi

echo === UNEXPECTED BLOCK ===

env

exit 200
