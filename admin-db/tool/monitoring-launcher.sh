#!/bin/bash

script_dir=$(realpath "$0")
script_dir=$(dirname "$script_dir")

# notify dir

notify_dir="$script_dir/notify.d"

# database

sql_client='psql'

db_table_instances='monitoring.instances'
db_table_scripts='monitoring.scripts'
db_table_targets='monitoring.targets'
db_table_types='monitoring.types'
db_table_log='monitoring.log'
db_table_series='monitoring.series'
db_table_notifications='monitoring.notifications'

# special uids

script_timeout_type_uid='script-timeout'
script_error_type_uid='script-error'

# delimeters

uid_delimeter="@"
list_delimeter=","
param_delimeter=$'\t'
new_line=$'\n'

# parameters

value="$1${param_delimeter}"
#echo 1: +$value+
while [ -n "$value" ]
do
	if [ -n "${value%%${param_delimeter}*}" ]
	then
		#echo +${value%%${param_delimeter}*}+
		export ${value%%${param_delimeter}*}
	fi
	value=${value#*${param_delimeter}}
done
unset value

# check configuration

if [ -z "$ADMIN_DB_HOST" ] || [ -z "$ADMIN_DB_PORT" ] || [ -z "$ADMIN_DB_NAME" ] || [ -z "$ADMIN_DB_USER" ] || [ -z "$ADMIN_DB_PSW" ]
then
	echo !!! Database connection parameters not found !!!
	echo "$1"
	env
	exit 201
fi

export PGPASSWORD="$ADMIN_DB_PSW"

sql_cmd="$sql_client -h $ADMIN_DB_HOST -p $ADMIN_DB_PORT -d $ADMIN_DB_NAME -U $ADMIN_DB_USER -t -c"


# ------------------------------
# Functions
# ------------------------------


###
# Kill process tree
#
# @param $pid - root process id
###

func_kill_tree () {

	echo === Kill process tree ===

	echo $pid - root PID

	local tree_pids=`pstree -l -p $pid | grep "([[:digit:]]*)" -o | tr -d '()'`
	#echo $tree_pids
	local kill_pids=""
	local p=""

	for p in $tree_pids
	do
		if ps -p $p > /dev/null
		then
			kill_pids="$kill_pids $p"
		fi
	done
	
	if [ -n "$kill_pids" ]
	then
		echo $kill_pids
		kill -INT $kill_pids
		wait $kill_pids 2> /dev/null
	else
		echo --- Nothing to kill ---
	fi

} # func_kill_tree


###
# Start duration period
#
# @return $start_uptime - duration period start uptime in ms
###

func_start_duration () {

	echo === Start duration ===

	start_uptime="$(cat /proc/uptime)"
	start_uptime="${start_uptime%% *}"
	start_uptime="${start_uptime//./}0"

	echo $start_uptime - start_uptime

} # func_start_duration


###
# Sets duration field of a table up to the current time
#
# @param $1 - table name
# @param $2 - record id
# @param $start_uptime - duration period start uptime in ms
#
# @return $code - error code
###

func_update_duration () {

	echo === Update duration ===

	echo $1 - table name
	echo $2 - record id
	echo $start_uptime - start uptime

	echo --- Get last duration ---

	local sql="SELECT duration FROM $1 WHERE id = $2"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	echo $sql ms

	echo --- Calculate current duration ---

	local uptime_now="$(cat /proc/uptime)"
	uptime_now="${uptime_now%% *}"
	uptime_now="${uptime_now//./}0"
	local duration=0
	let "duration = ($uptime_now - $start_uptime)"
	echo $duration ms

	if [ $sql -gt $duration ]
	then
		let "duration = $sql - (($sql - $duration) / 20)"
		echo $duration ms
	fi

	echo --- Update duration ---

	sql="UPDATE $1 SET duration = $duration WHERE id = $2"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	echo $sql

} # func_update_duration


###
# Inserts new series row
#
# @param $target_id - target_id or NULL
# @param $target_uid - script_uid${uid_delimeter}target_uid
# @param $target_name - script_name [target_name]
# @param $target_short_name - target_name
#
# @param $type_id - type_id
# @param $type_uid - type_uid
# @param $type_name - type_name
# @param $type_description - type_description, optional
#
# @param $object - object name and uid source, optional
# @param $description - script description, optional
#
# @param $value - integer value
#
# @return $code - error code
###

func_insert_series () {

	echo === Insert series ===

	local series_name="$target_name $type_name"
	local series_short_name="$target_short_name"

	local object_uid=""

	if [ -n "$object" ]
	then
		object_uid="${object//${uid_delimeter}/-}"
		object_uid="${object_uid//[[:space:]]/-}"

		series_name="$series_name [$object]"
		series_short_name="$series_short_name - $object"
	fi

	local series_uid="$target_uid${uid_delimeter}$type_uid${uid_delimeter}$object_uid"
	series_uid="${series_uid,,}"
	series_uid="'${series_uid//\'/\'\'}'"

	echo $series_uid

	local series_description=""
	if [ -n "$type_description" ]
	then
		series_description="$type_description"
	fi
	if [ -n "$description" ]
	then
		if [ -n "$series_description" ]
		then
			series_description="$series_description$new_line$description"
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

	echo --- Increment repetition ---

	local repetition=0
	local sql="SELECT value, repetition FROM $db_table_series WHERE uid = $series_uid ORDER BY time DESC LIMIT 1"
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

	echo --- Insert series row ---

	sql="INSERT INTO $db_table_series (target_id, uid, type_id, value, repetition, name, short_name, description) VALUES ($target_id, $series_uid, $type_id, $value, $repetition, '${series_name//\'/\'\'}', '${series_short_name//\'/\'\'}', $series_description)"
	#echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	echo $sql
	if [ $code != 0 ]
	then
		echo !!! $db_table_series insert error $code !!!
	fi

} # func_insert_series


###
# Returns type by UID.
# Adds it if not exists.
#
# @param $type_uid - type UID to find
# @param $alert_type_name - if $type_uid not found in the database
#
# @return $type_id - type_id
# @return $type_uid - type_uid
# @return $type_name - type_name
# @return $type_description - type_description, optional
#
# @return $code - error code
###

func_get_type () {

	echo === Get type ===

	type_id=""
	type_name=""
	type_description=""

	local sql="SELECT id, name, description FROM $db_table_types WHERE uid = '${type_uid//\'/\'\'}'"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	echo $sql

	if [ -z "$sql" ]
	then
		echo --- Insert type ---

		sql="INSERT INTO $db_table_types (uid, is_alert, name, notification_delay, notification_period, description) VALUES ('$type_uid', TRUE, '$alert_type_name', 0, 60, '$type_uid')"
		echo $sql
		sql=$($sql_cmd "$sql")
		code=$?
		echo $sql
		if [ $code != 0 ]
		then
			echo !!! $db_table_types insert error $code !!!
			return
		fi

		echo --- Get type again ---

		sql="SELECT id, name, description FROM $db_table_types WHERE uid = '$type_uid'"
		echo $sql
		sql=$($sql_cmd "$sql")
		code=$?
		sql="${sql#"${sql%%[![:space:]]*}"}"
		echo $sql
	fi

	if [ -z "$sql" ]
	then
		echo !!! Get type error $code !!!
		return
	fi

	type_id="${sql%% *}"
	sql="${sql#* | }"
	type_name="${sql%% |*}"
	sql="${sql#* |}"
	type_description="$(echo $sql | sed 's/\s*+\s|\s|\s/\n/g')"

	echo +$type_id+ type_id
	echo +$type_uid+ type_uid
	echo +$type_name+ type name
	echo +$type_description+ type description, optional

} # func_get_type


###
# Parse metric and insert series row
#
# @param $metric - metric line returned by a script
#
# @param $target_id - target_id
# @param $target_uid - script_uid${uid_delimeter}target_uid
# @param $target_name - script_name [target_name]
# @param $target_short_name - target_short_name
#
# @return $code - error code
###

func_parse_metric () {

	echo === Parse metric string ===

	echo $metric

	code=0

	local value=${metric%%\#*}
	metric=${metric#*\#}
	local type_uid=${metric%%\#*}
	metric=${metric#*\#}
	local object=${metric%%\#*}
	metric=${metric#*\#}
	local description=${metric%%\#*}

	value="${value//[[:space:]]/}"

	type_uid="${type_uid//${uid_delimeter}/}"
	type_uid="${type_uid//[[:space:]]/}"

	object="${object//${uid_delimeter}/|}"
	object="${object#"${object%%[![:space:]]*}"}"
	object="${object%"${object##*[![:space:]]}"}"

	description="${description//^/$new_line}"
	description="${description#"${description%%[![:space:]]*}"}"
	description="${description%"${description##*[![:space:]]}"}"

	echo +$value+ metric value
	echo +$type_uid+ type uid
	echo +$object+ metric object name, optional
	echo +$description+ metric description, optional

	if [ -n "$value" ] && [ -n "$type_uid" ]
	then
		local alert_type_name="$type_uid"

		func_get_type
		func_insert_series
	fi

} # func_parse_metric


###
# Returns command to run a script
#
# @param $script_path - script file is to run
#
# @return $script_cmd - command to run the script
# @return $code - error code
###

func_get_script_command () {

	echo === Script command ===

	echo $script_path

	local line=""
	code=0
	script_cmd=""

	IFS= read -r line < "$script_path"

	echo $line

	if [[ "$line" =~ ^#!\/.+\/bash[[:space:]]*$ ]]
	then
		echo --- Bash script ---

		script_cmd='bash "'$script_path'"'
	elif [[ "$line" =~ ^#!\/.+[[:space:]]+python.*$ ]]
	then
		echo --- Python script ---

		script_cmd='python3 "'$script_path'"'
	elif [[ "$line" =~ ^\<\?php[[:space:]]*$ ]]
	then
		echo --- PHP script ---

		script_cmd='php "'$script_path'"'
	elif [[ "$line" =~ ^#!\/.+\/perl[[:space:]]*$ ]]
	then
		echo --- Perl script ---

		script_cmd='perl "'$script_path'"'
	else
		echo !!! Unknown script format $script_id !!!
		code=205
	fi

} # func_get_script_command


###
# Executes command by a user
#
# @param $sudo_user - user to run the command of
# @param $script_path - script file path
# @param $out_path - file to save script output in
# @param $script_cmd - command to run the script, optional
#
# @return $script_cmd - command to run the script
# @return $code - error code
###

func_run_as () {

	echo === Run as ===

	if [ -n "$sudo_user" ] && [ "$(whoami)" == "$sudo_user" ]
	then
		sudo_user=""
	fi

	if [ -z "$script_cmd" ]
	then
		func_get_script_command
		if [ $code != 0 ]
		then
			return
		fi
	fi

	if [ -n "$sudo_user" ]
	then
		echo --- Exec by sudo user $sudo_user ---

		local run='sudo -E -u "'$sudo_user'" '$script_cmd' "'$1'" "'$2'"'
	else
		echo --- Exec by current user $(whoami) ---

		local run=$script_cmd' "'$1'" "'$2'"'
	fi

	echo $run
	if [ -n "$out_path" ]
	then
		eval $run 2> "$out_path" > "$out_path"
	else
		eval $run 2>&1
	fi
	code=$?

} # func_run_as


###
# Executes command
#
# @param $sudo_user - user to run the command of
# @param $temp_dir - script temp files directory
# @param $script_path - script file path
# @param $out_path - file to save script output in
# @param $script_cmd - command to run the script, optional
#
# @return $script_cmd - command to run the script
# @return $code - error code
###

func_run_command () {

	echo === Run command ===

	chmod -R 777 "$temp_dir/"
	ls -l "$temp_dir" | grep ":"

	func_run_as "$1" "$2"

} # func_run_command


###
# Run monitoring target of a script
#
# @param $script_id - script_id
# @param $target_id - target_id
# @param $temp_dir - script temp files directory
# @param $script_path - script file path
#
# @return $code - error code
###

func_run_target () {

	echo === Run target ===

	echo $script_id - script ID
	echo $target_id - target ID

	local start_uptime=""
	func_start_duration

	echo --- Get script data ---

	local sql="SELECT uid, name FROM $db_table_scripts WHERE id = $script_id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	echo $sql
	if [ -z "$sql" ]
	then
		echo !!! $db_table_scripts select error $code !!!
		return
	fi

	local script_uid="${sql%% *}"
	local script_name="${sql##* | }"

	echo +$script_uid+ script uid
	echo +$script_name+ script name

	echo --- Get target data ---

	sql="SELECT uid, name, target FROM $db_table_targets WHERE id = $target_id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	echo $sql
	if [ -z "$sql" ]
	then
		echo !!! $db_table_targets select error $code !!!
		return
	fi

	local target_uid="$script_uid${uid_delimeter}${sql%% | *}"
	target_uid="${target_uid%"${target_uid##*[![:space:]]}"}"
	sql="${sql#* | }"
	local target_short_name="${sql%% | *}"
	target_short_name="${target_short_name%"${target_short_name##*[![:space:]]}"}"
	local target_name="$script_name [$target_short_name]"
	local target="${sql##* | }"
	target="${target%"${target##*[![:space:]]}"}"

	echo +$target+ target
	echo +$target_uid+ target uid
	echo +$target_short_name+ target short name
	echo +$target_name+ target name

	echo --- Write data file ---

	local data_path="$temp_dir/data.$target_id"

	sql="SELECT script_data FROM $db_table_targets WHERE id = $target_id AND script_data IS NOT NULL"
	echo $sql
	echo $data_path
	$sql_cmd "$sql" | sed -e 's/^\s//' -e 's/\s*+$//' > "$data_path"
	#cat "$data_path"

	echo --- Execute script ---

	local out_path="$temp_dir/out.$target_id"
	touch "$out_path"

	local sudo_user="$MONITORING_USER"
	func_run_command "$target" "$data_path"
	local script_code=$code

	echo --- Write log ---

	sql="INSERT INTO $db_table_log (target_id, code, output) VALUES ($target_id, $script_code, '$(sed s/\'/\'\'/g "$out_path")')"
	#echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	echo $sql
	if [ $code != 0 ]
	then
		echo !!! $db_table_log insert error $code !!!
		return
	fi

	echo --- Script exit code $script_code ---

	local metrics=""

	if [ $script_code != 0 ]
	then
		local type_uid="$script_error_type_uid"
		local alert_type_name="SCRIPT ERROR CODE"

		func_get_type

		metrics=( "$script_code#$type_uid#" )
	else
		echo --- Load metrics ---

		mapfile -t metrics < <( sed -n -E '/METRIC#.+#METRIC/p' "$out_path" | sed -e 's/.*METRIC#//' -e 's/#METRIC.*/#/' )
	fi

	echo --- Parse metrics ---

	if [ ${#metrics[*]} != 0 ]
	then
		local metric=""

		for metric in "${metrics[@]}"
		do
			func_parse_metric
		done
	fi

	func_update_duration "$db_table_targets" "$target_id"

} # func_run_target


###
# Run monitoring target list of a script
#
# @param $script_id - script_id
# @param $target_ids - list of target_id
# @param $temp_dir - script temp files directory, optional
#
# @return $code - error code
###

func_run_targets () {

	echo === Run target list ===

	echo $script_id - script ID
	echo $target_ids - target IDs

	if [ -z "$temp_dir" ] || [ ! -d "$temp_dir" ]
	then
		echo --- Create script temp directory ---

		local temp_dir=$(mktemp -d)
		echo $temp_dir
		if [ ! -d "$temp_dir" ]
		then
			echo !!! Can not create temp directory !!!
			code=203
			return
		fi
	fi

	echo --- Write script file ---

	local script_path="$temp_dir/script"

	local sql="SELECT script FROM $db_table_scripts WHERE id = $script_id"
	echo $sql
	$sql_cmd "$sql" | sed -e 's/^\s//' -e 's/\s*+$//' > "$script_path"

	code=0

	if [ ! -r "$script_path" ]
	then
		echo !!! Can not write script file !!!
		code=204
	fi

	if [ $code == 0 ]
	then
		local target_id=0
		local script_cmd=""

		for target_id in ${target_ids//${list_delimeter}/ }
		do
			func_run_target
		done
	fi

	if [ -e "$temp_dir" ]
	then
		echo --- Remove script temp directory ---

		rm -r "$temp_dir"
	fi

} # func_run_targets


###
# Run monitoring target list of a script
# and fix execution duration
#
# @param $script_id - script_id
# @param $target_ids - list of target_id
#
# @return $code - error code
###

func_run_targets_duration () {

	local start_uptime=""
	func_start_duration

	func_run_targets

	func_update_duration "$db_table_scripts" "$script_id"

} # func_run_targets_duration


###
# Run all targets of a script in separate process
#
# @param $instance_id - instance ID
# @param $script_id - script_id
# @param $run_count - instance run count
# @param $temp_dir - instance temp files directory
#
# @return $pid - the process PID of the running script
# @return $code - error code
###

func_run_script () {

	echo === Run script ===

	echo $instance_id - instance ID
	echo $script_id - script ID

	echo --- Get target ids ---

	local sql="SELECT id, period FROM $db_table_targets WHERE script_id = $script_id AND instance_id = $instance_id AND enabled"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]
	then
		echo !!! $db_table_targets select error $code !!!
		return
	fi

	sql="${sql#"${sql%%[![:space:]]*}"}"
	sql="${sql// |/}"

	echo --- Period filter ---

	local target_ids=""
	local id=""

	local period=0

	for period in $sql
	do
		if [ -z "$id" ]
		then
			id=$period
		else
			let "period = ($run_count % $period) - ($id % $period)"

			if [ $period == 0 ]
			then
				target_ids="$target_ids $id"
			fi

			id=""
		fi
	done

	pid=""

	if [ -n "$target_ids" ]
	then
		echo --- Target list ---
		
		target_ids="${target_ids#"${target_ids%%[![:space:]]*}"}"
		echo $target_ids

		echo --- Create script temp directory ---

		local temp_dir=$temp_dir/$script_id
		if [ -e "$temp_dir" ]
		then
			rm -r "$temp_dir"
		fi
		mkdir -p "$temp_dir"
		echo $temp_dir
		if [ ! -d "$temp_dir" ]
		then
			echo !!! Can not create temp directory !!!
			code=203
			return
		fi

		. "$0" "$1${param_delimeter}temp_dir=$temp_dir${param_delimeter}script_id=$script_id${param_delimeter}target_ids=${target_ids// /${list_delimeter}}${param_delimeter}call_function=func_run_targets_duration" &
		pid=$!
		echo $pid > "$temp_dir/pid"

		echo --- Started script $script_id PID $pid ---
	fi

} # func_run_script


###
# Run timeout process to kill scripts
#
# @param $script_timeout - scripts timeout in seconds
# @param $temp_dir - instance temp files directory
#
# @return $code - error code
###

func_timeout_process () {

	echo === Run timeout process ===

	echo $script_timeout - script_timeout
	sleep $script_timeout

	local type_uid="$script_timeout_type_uid"
	local alert_type_name="SCRIPT TIMEOUT"

	func_get_type

	local description=""
	local object=""
	local target_id=NULL
	local target_uid=""
	local target_name=""
	local target_short_name="*"
	local value=1

	local timeout_pair=""
	local pid=""
	local script_id=0
	local sql=""

	code=0

	echo --- Kill scripts ---

	for script_id in $(ls -F -1 "$temp_dir" | sed -n "s/\///p")
	do
		echo --- script_id $script_id ---
		
		pid=$(cat "$temp_dir/$script_id/pid")
		
		echo --- pid $pid ---

		if ps -p $pid > /dev/null
		then
			echo --- Insert alert on script $script_id ---

			sql="SELECT uid, name FROM $db_table_scripts WHERE id = $script_id"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			sql="${sql#"${sql%%[![:space:]]*}"}"
			echo $sql
			if [ -z "$sql" ]
			then
				echo !!! $db_table_scripts select error $code !!!
			else
				target_uid="${sql%% *}${uid_delimeter}$target_short_name"
				target_name="${sql##* | } [$target_short_name]"

				echo $target_uid - target uid
				echo $target_name - target name

				func_insert_series
			fi

			func_kill_tree
		fi
	done

} # func_timeout_process


###
# Run all scripts of an instance in separate processes
#
# @param $instance_id - instance ID
# @param $run_count - instance run count
# @param $script_timeout - scripts timeout in seconds
# @param $temp_dir - instance temp files directory
#
# @return $code - error code
###

func_run_scripts () {

	echo === Run scripts ===

	echo $instance_id - instance_id
	echo $run_count - run_count
	echo $script_timeout - script_timeout

	echo --- Get script ids ---

	local sql="SELECT s.id FROM $db_table_scripts s INNER JOIN $db_table_targets t ON s.id = t.script_id WHERE t.instance_id = $instance_id AND t.enabled AND s.enabled GROUP BY s.id"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	if [ $code != 0 ]
	then
		echo !!! $db_table_scripts select error $code !!!
		return
	fi

	sql="${sql#"${sql%%[![:space:]]*}"}"
	echo $sql

	local pids=""
	local timeout_pids=""
	local script_id=0
	local pid=""

	for script_id in $sql
	do
		func_run_script

		if [ -n "$pid" ]
		then
			pids="$pids $pid"
		fi
	done

	if [ -n "$pids" ]
	then
		. "$0" "$1${param_delimeter}temp_dir=$temp_dir${param_delimeter}script_timeout=$script_timeout${param_delimeter}call_function=func_timeout_process" &
		pid=$!

		wait $pids 2> /dev/null

		echo --- Scripts ended - kill timeout ---

		func_kill_tree
	fi

} # func_run_scripts


###
# Run all scripts of an instance
# and clean old data
#
# @param $instance_id - instance ID
# @param $run_count - instance run count
# @param $script_timeout - scripts timeout in seconds
#
# @return $code - error code
###

func_run_scripts_clean () {

	func_run_scripts

	echo --- Clean up log ---

	local sql="DELETE FROM $db_table_log WHERE time < NOW() - INTERVAL '3 days'"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	echo $sql
	if [ $code != 0 ]; then
		echo !!! $db_table_log delete error $code !!!
	fi

	echo --- Clean up series ---

	sql="DELETE FROM $db_table_series WHERE time < NOW() - INTERVAL '7 days'"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	echo $sql
	if [ $code != 0 ]; then
		echo !!! $db_table_series delete error $code !!!
	fi

} # func_run_scripts_clean


###
# Process notifications
#
# @return $code - error code
###

func_notify () {

	echo === Notify ===

	echo $notify_dir

	if [ ! -d "$notify_dir" ]
	then
		mkdir -p "$notify_dir"
	fi

	code=0

	if [ -d "$notify_dir" ]
	then
		echo --- Notify loop ---

		local sql=""
		local id=""
		local value=""
		local repetition=""
		local notification_delay=""
		local notification_period=""

		while true
		do
			echo --- Fetch oldest notification ---

			sql="SELECT id, value, repetition, notification_delay, notification_period FROM $db_table_notifications ORDER BY time ASC LIMIT 1"
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
			sql="${sql#* |}"
			value="${sql%% |*}"
			value="${value#"${value%%[![:space:]]*}"}"
			sql="${sql#* |}"
			repetition="${sql%% |*}"
			repetition="${repetition#"${repetition%%[![:space:]]*}"}"
			sql="${sql#* |}"
			notification_delay="${sql%% |*}"
			notification_delay="${notification_delay#"${notification_delay%%[![:space:]]*}"}"
			sql="${sql#* |}"
			notification_period="${sql#"${sql%%[![:space:]]*}"}"

			echo +$id+ id
			echo +$value+ value
			echo +$repetition+ repetition
			echo +$notification_delay+ notification delay
			echo +$notification_period+ notification period

			echo --- Remove the notification from queue ---

			sql="UPDATE $db_table_notifications SET notified = TRUE WHERE id = $id"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			echo $sql

			if [ $code != 0 ]; then
				echo !!! Notification update error !!!
				break
			fi

			if [ ${sql##* } == 0 ]; then
				echo --- The notification has gone from queue - ignore it ---
				continue
			fi

			echo --- Notification delay and period filter ---

			if [ "$notification_period" -gt "0" ] && [ "$value" != "0" ]
			then
				let "repetition = ($repetition % $notification_period)"
			fi

			echo +$repetition+ repetition
			echo +$notification_delay+ notification delay

			if [ "$repetition" != "$notification_delay" ]
			then
				continue
			fi

			echo --- Do notification ---

			sql="SELECT uid, value, name, description FROM $db_table_series WHERE id = $id"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			sql="${sql#"${sql%%[![:space:]]*}"}"
			echo $sql

			export notification_uid="${sql%% *}"
			sql="${sql#* |}"
			local notification_value="${sql%% |*}"
			export notification_value="${notification_value#"${notification_value%%[![:space:]]*}"}"
			sql="${sql#* |}"
			local notification_name="${sql%% |*}"
			export notification_name="${notification_name#"${notification_name%%[![:space:]]*}"}"
			sql="${sql#* |}"
			export notification_description="$(echo $sql | sed 's/\s*+\s|\s|\s|\s/\n/g')"

			echo +$notification_uid+ notification uid
			echo +$notification_value+ notification value
			echo +$notification_name+ notification name
			echo +$notification_description+ notification description

			local script_path=""
			local sudo_user=""
			local out_path=""

			find "$notify_dir/" -maxdepth 1 -type f | sort |
			while IFS= read -r script_path
			do
				local script_cmd=""
				func_run_as
			done

			sleep 1
		done
	else
		echo !!! No access to notification directory !!!
		code=202
	fi

} # func_notify


###
# Process instance
#
# @return $code - error code
###

func_instance () {

	echo === Instance ===

	if [ -z "$MONITORING_INSTANCE" ]
	then
		MONITORING_INSTANCE=$(hostname)
	fi
	echo $MONITORING_INSTANCE

	code=0

	echo --- Double run lock ---

	local temp_dir=/tmp/$(basename "$0").$MONITORING_INSTANCE
	
	if [ -r "$temp_dir/pid" ] && ps -p $(cat "$temp_dir/pid") > /dev/null
	then
		echo !!! The script is already running - PID $(cat "$temp_dir/pid") !!!
		code=206
		return
	fi

	echo --- Create instance temp directory ---

	echo $temp_dir
	if [ -e "$temp_dir" ]
	then
		rm -r "$temp_dir"
	fi
	mkdir -p "$temp_dir"
	if [ ! -d "$temp_dir" ]
	then
		echo !!! Can not create temp directory !!!
		code=203
		return
	fi
	echo $$ | tee "$temp_dir/pid"

	echo --- Get instance id of $MONITORING_INSTANCE ---

	local sql="SELECT id, run_count, script_timeout FROM $db_table_instances WHERE instance = '${MONITORING_INSTANCE//\'/\'\'}' AND enabled"
	echo $sql
	sql=$($sql_cmd "$sql")
	code=$?
	sql="${sql#"${sql%%[![:space:]]*}"}"
	if [ -z "$sql" ]; then
		echo --- Instance not found ---
	else
		local instance_id="${sql%% *}"
		sql="${sql#* |}"
		local run_count="${sql%% |*}"
		run_count="${run_count#"${run_count%%[![:space:]]*}"}"
		sql="${sql#* |}"
		local script_timeout="${sql#"${sql%%[![:space:]]*}"}"

		echo +$instance_id+ instance_id
		echo +$run_count+ run_count
		echo +$script_timeout+ script_timeout

		local start_uptime=""
		func_start_duration

		let "run_count = ($run_count + 1) % 3628800"

		echo --- Update run count ---

		sql="UPDATE $db_table_instances SET run_count = $run_count, is_running = TRUE WHERE id = $instance_id"
		echo $sql
		sql=$($sql_cmd "$sql")
		code=$?
		if [ $code == 0 ]; then

			func_run_scripts_clean

			echo --- Clear running flag ---

			sql="UPDATE $db_table_instances SET is_running = FALSE WHERE id = $instance_id"
			echo $sql
			sql=$($sql_cmd "$sql")
			code=$?
			echo $sql

		else
			echo !!! $db_table_instances update error $code !!!
		fi

		func_update_duration "$db_table_instances" "$instance_id"
	fi

	if [ -e "$temp_dir" ]
	then
		echo --- Remove instance temp directory ---

		rm -r "$temp_dir"
	fi

} # func_instance


# ------------------------------
# Main code
# ------------------------------

if [ -n "$call_function" ]
then
	$call_function
else
	func_instance
fi

exit $code
