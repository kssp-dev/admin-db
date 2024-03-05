<?php

$app_dir = __DIR__ . '/';

$server_root_length = strlen($_SERVER['DOCUMENT_ROOT']);

if ($server_root_length <= 0) {
	print_r('HTTP server required');
	exit(1);
}

$app_uri = substr_replace($app_dir, '', 0, $server_root_length);


require_once $app_dir . '../vendor/autoload.php';

foreach (glob($app_dir . 'ui/*.php') as $file) {
    require_once $file;
}

//error_log(print_r("_SERVER " . print_r($_SERVER, true), true));

require_once 'db.php';

foreach (glob($app_dir . 'model/*.php') as $file) {
    require_once $file;
}

foreach (glob($app_dir . 'app/*.php') as $file) {
    require_once $file;
}

$app = new AdminDbApp();

?>
