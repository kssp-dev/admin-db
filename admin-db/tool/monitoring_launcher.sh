#!/bin/bash

script_dir=$(realpath "$0")
script_dir=$(dirname "$script_dir")

# notify dir

notify_dir="$script_dir/notify.d"

# database connection

sql_client='psql'

db_table_instances='monitoring.instances'
db_table_scripts='monitoring.scripts'
db_table_targets='monitoring.targets'
db_table_types='monitoring.types'
db_table_log='monitoring.log'
db_table_series='monitoring.series'
db_table_notifications='monitoring.notifications'

# unknown type uid

unknown_type_uid='unknown-type'

# parameters

id_delimeter="@"
param_delimeter=$'\t'
new_line=$'\n'

value="$1${param_delimeter}"
length=0
while [ "${#value}" != "$length" ]
do
	length=${#value}
	value="${value//${param_delimeter}${param_delimeter}/${param_delimeter}}"
done
while [ -n "${value%%${param_delimeter}*}" ]
do
	export ${value%%${param_delimeter}*}
	value=${value#*${param_delimeter}}
done

# check configuration
if [ -z "$ADMIN_DB_HOST" ] || [ -z "$ADMIN_DB_PORT" ] || [ -z "$ADMIN_DB_NAME" ] || [ -z "$ADMIN_DB_USER" ] || [ -z "$ADMIN_DB_PSW" ]
then
	echo "Database connection parameters not found"
	echo "$1"
	env
	exit 222
fi

export PGPASSWORD="$ADMIN_DB_PSW"

sql_cmd="$sql_client -h $ADMIN_DB_HOST -p $ADMIN_DB_PORT -d $ADMIN_DB_NAME -U $ADMIN_DB_USER -t -c"


# ------------------------------
# Instance stage
# ------------------------------

if [ -z "$instance_id" ] && [ -z "$script_id" ]
then
	echo === STAGE 1 ===

	echo --- Instance ---

	if [ -z "$MONITORING_INSTANCE"]
	then
		MONITORING_INSTANCE=$(hostname)
	fi
	echo $MONITORING_INSTANCE

	echo --- Double run lock ---

	pid_file=$(basename "$0")
	pid_file=/tmp/$pid_file.$MONITORING_INSTANCE.pid
	echo $pid_file
	if [ -r "$pid_file" ] && ps -p $(cat "$pid_file") > /dev/null
	then
		echo The script has already run
		exit
	fi
	echo $$ | tee "$pid_file"

	echo --- Get instance id of $MONITORING_INSTANCE ---

	sql="SELECT id, run_count FROM $db_table_instances WHERE instance = '${MONITORING_INSTANCE//\'/\'\'}' AND enabled"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ -n "$sql" ]; then
		instance_id="${sql%% *}"
		run_count="${sql##* }"

		let "run_count = ($run_count + 1) % 3628800"

		echo --- Update run count ---

		sql="UPDATE $db_table_instances SET run_count = $run_count, is_running = TRUE WHERE id = $instance_id"
		echo $sql
		sql=$($sql_cmd "$sql")
		code=$?
		if [ $code == 0 ]; then
			echo --- Start scripts of instance $instance_id ---

			param="instance_id=$instance_id${param_delimeter}run_count=$run_count"
			param="$param${param_delimeter}ADMIN_DB_HOST=$ADMIN_DB_HOST"
			param="$param${param_delimeter}ADMIN_DB_PORT=$ADMIN_DB_PORT"
			param="$param${param_delimeter}ADMIN_DB_NAME=$ADMIN_DB_NAME"
			param="$param${param_delimeter}ADMIN_DB_USER=$ADMIN_DB_USER"
			param="$param${param_delimeter}ADMIN_DB_PSW=$ADMIN_DB_PSW"
			param="$param${param_delimeter}MONITORING_USER=$MONITORING_USER"
			param="$param${param_delimeter}MONITORING_INSTANCE=$MONITORING_INSTANCE"

			. "$0" "$param"

			echo --- Clean up log ---

			sql="DELETE FROM $db_table_log WHERE time < NOW() - INTERVAL '3 days'"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			echo $sql
			if [ $code != 0 ]; then
				echo SQL DELETE Error $code
			fi

			echo --- Clean up series ---

			sql="DELETE FROM $db_table_series WHERE time < NOW() - INTERVAL '7 days'"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			echo $sql
			if [ $code != 0 ]; then
				echo SQL DELETE Error $code
			fi

			echo --- Notify dir ---

			echo $notify_dir

			if [ ! -d "$notify_dir" ]
			then
				mkdir -p "$notify_dir"
			fi

			if [ -d "$notify_dir" ]
			then
				echo --- Notify loop ---

				while true
				do
					echo --- Fetch oldest notification ---

					sql="SELECT id, repetition, notification_delay, notification_period FROM $db_table_notifications ORDER BY time ASC LIMIT 1"
					echo $sql
					sql=$($sql_cmd "$sql")
					code=$?
					sql="${sql#"${sql%%[![:space:]]*}"}"
					if [ -z "$sql" ]
					then
						echo --- Notification queue is empty ---
						break
					fi
					echo $sql

					id="${sql%% *}"
					sql="${sql#* | }"
					repetition="${sql%% |*}"
					sql="${sql#* | }"
					notification_delay="${sql%% |*}"
					sql="${sql#* | }"
					notification_period="${sql%% |*}"

					echo $id - id
					echo $repetition - repetition
					echo $notification_delay - notification delay
					echo $notification_period - notification period

					echo --- Remove the notification from queue ---

					sql="UPDATE $db_table_notifications SET notified = TRUE WHERE id = $id"
					echo $sql
					sql=$($sql_cmd "$sql")
					code=$?
					echo $sql

					if [ $code != 0 ]; then
						echo --- Notification update error ---
						break
					fi

					if [ ${sql##* } == 0 ]; then
						echo --- The notification has gone from queue - ignore it ---
						continue
					fi

					echo --- Notification delay and period filter ---

					let "repetition = ($repetition % $notification_period)"

					if [ $repetition != $notification_delay ]
					then
						continue
					fi

					echo --- Do notification ---

					sql="SELECT uid, value, name, description FROM $db_table_series WHERE id = $id"
					echo $sql
					sql=$($sql_cmd "$sql")
					code=$?
					sql="${sql#"${sql%%[![:space:]]*}"}"
					#echo $sql

					export notification_uid="${sql%% *}"
					sql="${sql#* | }"
					export notification_value="${sql%% *}"
					sql="${sql#* | }"
					export notification_name="${sql%% |*}"
					sql="${sql#* |}"
					export notification_description="$(echo $sql | sed 's/\s*+\s|\s|\s/\n/g')"

					echo $notification_uid - notification uid
					echo $notification_value - notification value
					echo $notification_name - notification name
					echo $notification_description - notification description

					find "$notify_dir/" -maxdepth 1 -type f -iname '*.sh' | sort | xargs bash

					sleep 1
				done
			fi

			echo --- Clear running flag ---

			sql="UPDATE $db_table_instances SET is_running = FALSE WHERE id = $instance_id"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			echo $sql
		fi
	fi

	echo --- Remove PID file ---

	rm "$pid_file"

	exit $code
fi


# ------------------------------
# Scripts stage
# ------------------------------

if [ -n "$instance_id" ] && [ -n "$run_count" ] && [ -z "$script_id" ]
then
	echo === STAGE 2 ===

	echo --- Get script ids ---

	sql="SELECT id FROM $db_table_scripts WHERE instance_id = $instance_id AND enabled"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]; then exit $code; fi
	sql="${sql#"${sql%%[![:space:]]*}"}"

	pids=""

	for script_id in $sql
	do
		echo --- Get target ids of script $script_id ---

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
				let "period = ($run_count % $period) - ($id % $period)"

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

			. "$0" "script_id=$script_id${param_delimeter}target_ids=$target_ids${param_delimeter}$1" &
			pid=$!

			echo --- Started script $script_id on targets $target_ids PID $pid ---

			pids="$pids $pid"
		fi
	done

	if [ -n "$pids" ]
	then
		wait $pids
	fi

	return
fi


# ------------------------------
# Script stage
# ------------------------------

if [ -n "$script_id" ] && [ -n "$target_ids" ] && [ -z "$target_id" ]
then
	echo === STAGE 3 ===
	echo $(date +%T.%N)

	temp_dir=$(mktemp -d)
	if [ ! -d "$temp_dir" ]
	then
		echo "Can not create temp directory"
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
		echo "Can not write script file"
		code=202
	fi

	if [ $code == 0 ]
	then
		echo --- Write script runner ---

		runner_path="$script_path.sh"

		echo '#!/bin/bash
		' > "$runner_path"

		IFS= read -r line < "$script_path"

		echo $line

		if [[ "$line" =~ ^#!\/.+\/bash[[:space:]]*$ ]]
		then
			echo --- Bash script $script_id ---

			echo bash '"'$script_path'"' '"$1"' '"$2"
			' >> "$runner_path"
		elif [[ "$line" =~ ^#!\/.+[[:space:]]+python.*$ ]]
		then
			echo --- Python script $script_id ---

			echo python3 '"'$script_path'"' '"$1"' '"$2"
			' >> "$runner_path"
		elif [[ "$line" =~ ^\<\?php[[:space:]]*$ ]]
		then
			echo --- PHP script $script_id ---

			echo php '"'$script_path'"' '"$1"' '"$2"
			' >> "$runner_path"
		elif [[ "$line" =~ ^#!\/.+\/perl[[:space:]]*$ ]]
		then
			echo --- Perl script $script_id ---

			echo perl '"'$script_path'"' '"$1"' '"$2"
			' >> "$runner_path"
		else
			echo "Unknown script format $script_id"
			cat "$script_path"
			code=203
		fi

		echo 'exit $?' >> "$runner_path"
	fi

	if [ $code == 0 ]
	then
		cat "$runner_path"

		for id in $target_ids
		do
			echo --- Start target $id ---

			. "$0" "target_id=$id${param_delimeter}temp_dir=$temp_dir${param_delimeter}$1"
		done
	fi

	if [ -d "$temp_dir" ]
	then
		echo --- Remove temp directory ---

		rm -r "$temp_dir"
	fi

	echo $(date +%T.%N)

	exit $code
fi


# ------------------------------
# Target stage
# ------------------------------

if [ -n "$script_id" ] && [ -n "$target_id" ] && [ -n "$temp_dir" ]
then
	echo === STAGE 4 ===

	echo --- Script $script_id ---

	sql="SELECT uid, name FROM $db_table_scripts WHERE id = $script_id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	echo $sql
	if [ -z "$sql" ]; then exit $code; fi

	export script_uid="${sql%% *}"
	export script_name="${sql##* | }"

	echo $script_uid - script uid
	echo $script_name - script name

	echo --- Target $target_id ---

	sql="SELECT uid, name, target FROM $db_table_targets WHERE id = $target_id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	echo $sql
	if [ -z "$sql" ]; then exit $code; fi

	target_uid="$script_uid${id_delimeter}${sql%% | *}"
	export target_uid="${target_uid%"${target_uid##*[![:space:]]}"}"
	sql="${sql#* | }"
	target_short_name="${sql%% | *}"
	export target_short_name="${target_short_name%"${target_short_name##*[![:space:]]}"}"
	export target_name="$script_name [$target_short_name]"
	target="${sql##* | }"
	target="${target%"${target##*[![:space:]]}"}"

	echo $target - target
	echo $target_uid - target uid
	echo $target_short_name - target short name
	echo $target_name - target name

	echo --- Write data file ---

	data_path="$temp_dir/data.$target_id"

	sql="SELECT script_data FROM $db_table_targets WHERE id = $target_id AND script_data IS NOT NULL"
	echo $sql
	echo $data_path
	$sql_cmd "$sql" | sed -e 's/^\s//' -e 's/\s*+$//' > "$data_path"
	#cat "$data_path"

	echo --- Execute script ---

	out_path="$temp_dir/out.$target_id"
	touch "$out_path"

	sudo_user="$MONITORING_USER"

	if [ "$(whoami)" == "$MONITORING_USER" ]
	then
		sudo_user=""
	fi

	if [ -n "$sudo_user" ]
	then
		chmod -R 777 "$temp_dir/"
	fi

	ls -l "$temp_dir"

	if [ -n "$sudo_user" ]
	then
		echo --- Exec by sudo user $sudo_user ---

		cmd='sudo -E -u "'$sudo_user'" bash "'$temp_dir/script.sh'" "'$target'" "'$data_path'"'
	else
		echo --- Exec by current user $(whoami) ---

		cmd='bash "'$temp_dir/script.sh'" "'$target'" "'$data_path'"'
	fi

	echo $(date +%T.%N)

	echo $cmd
	eval $cmd 2>&1 > "$out_path"
	script_code=$?

	echo $(date +%T.%N)


	echo --- Write log ---

	sql="INSERT INTO $db_table_log (target_id, code, output) VALUES ($target_id, $script_code, '$(sed s/\'/\'\'/g "$out_path")')"
	#echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	echo $sql
	if [ $code != 0 ]; then exit $code; fi

	echo --- Script exit code $script_code ---

	if [ $script_code != 0 ]
	then
		metrics=( "$script_code#monitoring-script-error#" )
	else
		echo --- Load metrics ---

		mapfile -t metrics < <( sed -n -E '/METRIC#.+#METRIC/p' "$out_path" | sed -e 's/.*METRIC#//' -e 's/#METRIC.*/#/' )
	fi

	if [ ${#metrics[*]} == 0 ]
	then
		echo --- No metric found ---

		return
	fi

	echo --- Parse metrics ---

	for line in "${metrics[@]}"
	do
		echo --- Metric string $line ---

		value=${line%%\#*}
		line=${line#*\#}
		type_uid=${line%%\#*}
		line=${line#*\#}
		object=${line%%\#*}
		line=${line#*\#}
		description=${line%%\#*}

		value="${value//[[:space:]]/}"

		type_uid="${type_uid//${id_delimeter}/}"
		type_uid="${type_uid//[[:space:]]/}"

		object="${object//${id_delimeter}/|}"
		object="${object#"${object%%[![:space:]]*}"}"
		object="${object%"${object##*[![:space:]]}"}"

		description="${description//^/$new_line}"
		description="${description#"${description%%[![:space:]]*}"}"
		description="${description%"${description##*[![:space:]]}"}"

		echo $value - metric value
		echo $type_uid - type uid
		echo $object - metric object name, optional
		echo $description - metric description, optional

		if [ -n "$value" ] && [ -n "$type_uid" ]
		then
			echo --- Get type $type_uid ---

			sql="SELECT id, name, description FROM $db_table_types WHERE uid = '${type_uid//\'/\'\'}'"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			sql="${sql#"${sql%%[![:space:]]*}"}"
			echo $sql

			if [ -z "$sql" ]
			then
				echo --- Type not found ---

				sql="SELECT id, name, description FROM $db_table_types WHERE uid = '$unknown_type_uid'"
				echo $sql
				sql=$($sql_cmd "$sql")
				code=$?
				sql="${sql#"${sql%%[![:space:]]*}"}"
				echo $sql
			fi

			if [ -z "$sql" ]
			then
				echo --- Insert unknown type ---

				sql="INSERT INTO $db_table_types (uid, is_alert, name, notification_delay, notification_period) VALUES ('$unknown_type_uid', TRUE, 'UNKNOWN TYPE', 0, 60)"
				echo $sql
				sql=$($sql_cmd "$sql")
				code=$?
				echo $sql
				if [ $code != 0 ]; then exit $code; fi

				sql="SELECT id, name, description FROM $db_table_types WHERE uid = '$unknown_type_uid'"
				echo $sql
				sql=$($sql_cmd "$sql")
				code=$?
				sql="${sql#"${sql%%[![:space:]]*}"}"
				echo $sql
			fi

			if [ -z "$sql" ]
			then
				echo --- Unknown type error ---
				exit 210
			fi

			type_id="${sql%% *}"
			sql="${sql#* | }"
			type_name="${sql%% |*}"
			sql="${sql#* |}"
			type_description="$(echo $sql | sed 's/\s*+\s|\s|\s/\n/g')"

			echo $type_id - type_id
			echo $type_name - type name
			echo $type_description - type description, optional

			series_name="$target_name $type_name"
			series_short_name="$target_short_name"
			object_id=""

			if [ -n "$object" ]
			then
				object_id="${id_delimeter}${object//${id_delimeter}/-}"
				object_id="${object_id//[[:space:]]/-}"

				series_name="$series_name [$object]"
				series_short_name="$series_short_name - $object"
			fi

			series_uid="$target_uid${id_delimeter}$type_uid$object_id"
			series_uid="${series_uid,,}"
			series_uid="'${series_uid//\'/\'\'}'"

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

			echo --- Increment repetition of $series_uid ---

			repetition=0
			sql="SELECT value, repetition FROM $db_table_series WHERE uid = $series_uid ORDER BY time DESC LIMIT 1"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			if [ -n "$sql" ]
			then
				sql="${sql#"${sql%%[![:space:]]*}"}"
				if [ "$value" == "${sql%% *}" ]
				then
					let "repetition = ${sql##* | } + 1"
				fi
			fi
			echo $repetition

			echo --- Add series row of $series_uid ---

			sql="INSERT INTO $db_table_series (target_id, uid, type_id, value, repetition, name, short_name, description) VALUES ($target_id, $series_uid, $type_id, $value, $repetition, '${series_name//\'/\'\'}', '${series_short_name//\'/\'\'}', $series_description)"
			#echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			echo $sql
			if [ $code != 0 ]; then exit $code; fi
		fi
	done

	return
fi

echo === UNEXPECTED BLOCK - WILL NEVER EXECUTE ===

env

exit 200
