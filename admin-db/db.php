<?php

$db_dsn = getenv('ADMIN_DB_DSN');
$db_user = getenv('ADMIN_DB_USER');
$db_psw = getenv('ADMIN_DB_PSW');

//error_log(print_r("ADMIN_DB_DSN -" . $db_dsn . "-", true));

if (empty($db_dsn)) {
	exit('ADMIN_DB_DSN environment variable MUST be declared');
}
if (empty($db_user)) {
	exit('ADMIN_DB_USER environment variable MUST be declared');
}
if (empty($db_psw)) {
	exit('ADMIN_DB_PSW environment variable MUST be declared');
}

?>
