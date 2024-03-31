<?php

$environment = [
	'ADMIN_DB_HOST'
	, 'ADMIN_DB_PORT'
	, 'ADMIN_DB_NAME'
	, 'ADMIN_DB_USER'
	, 'ADMIN_DB_PSW'
	, 'MONITORING_USER'
	, 'script_id'
	, 'target_ids'
];

//print_r('getenv ' . print_r(getenv(), true));
//print_r('_ENV ' . print_r($_ENV, true));
//print_r('_SERVER ' . print_r($_SERVER, true));

putenv('script_id=' . $argv[1]);
putenv('target_ids=' . $argv[2]);

foreach ($environment as $var) {
	if (empty(getenv($var))) {
		putenv($var . '=' . $_SERVER[$var]);
	}
}

//print_r('Exec ' . print_r(getenv(), true));

$param='';
foreach ($environment as $var) {
	$param = $param . $var . '=' . getenv($var) . "\t";
}

$cmd = 'bash "' . __DIR__  . '/monitoring_main.sh" "' . $param . '" 2>&1';
//print_r($cmd . "\n");

$code = 0;
$res = passthru($cmd, $code);

exit($code);

?>
