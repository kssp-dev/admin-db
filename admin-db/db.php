<?php

$db_host = getenv('ADMIN_DB_HOST');
$db_port = getenv('ADMIN_DB_PORT');
$db_name = getenv('ADMIN_DB_NAME');

$db_user = getenv('ADMIN_DB_USER');
$db_psw = getenv('ADMIN_DB_PSW');

if (!empty($db_host) && !empty($db_port) && !empty($db_name)) {
	$db_dsn = 'pdo_pgsql:host=' . $db_host . ';port='. $db_port . ';dbname=' . $db_name;
} else {
	exit('ADMIN_DB_HOST, ADMIN_DB_PORT, ADMIN_DB_NAME environment variables MUST be declared');
}

if (empty($db_user)) {
	exit('ADMIN_DB_USER environment variable MUST be declared');
}
if (empty($db_psw)) {
	exit('ADMIN_DB_PSW environment variable MUST be declared');
}

?>
