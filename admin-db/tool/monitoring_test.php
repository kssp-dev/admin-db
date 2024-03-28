<?php

//print_r(getenv());

putenv('script_id=' . $argv[1]);
putenv('target_ids=' . $argv[2]);


$code = 0;
$res = passthru(
	'bash "' . __DIR__  . '/monitoring_main.sh" 2>&1'
, $code);

?>
