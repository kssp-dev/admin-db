<?php

// Global variables

$app_dir = __DIR__ . '/';

//error_log(print_r("_POST " . print_r($_POST, true), true));
//error_log(print_r("_SERVER " . print_r($_SERVER, true), true));

$server_root_length = strlen($_SERVER['DOCUMENT_ROOT']);
$tab_uri = $_SERVER['PHP_SELF'];
$query_string = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;

if ($server_root_length <= 0 || empty($tab_uri)) {
	print_r('HTTP server required');
	exit(1);
}

$app_uri = substr_replace($app_dir, '', 0, $server_root_length);

// Features

$features = getenv('ADMIN_DB_FEATURES');
if (! $features) {
	$features = 'monitoring admin';
}
$features = preg_replace('/\W+/', ' ', $features);
$features = preg_replace('/^\s+/', '', $features);
$features = preg_replace('/\s+$/', '', $features);
$array = explode(' ', $features);
$features = [];
foreach ($array as $feature) {
	$features[$feature] = true;
}
unset($array);

// Home tab redirect

if (!isset($title)) {
	if ($features['monitoring']) {
		header("Location: " . $app_uri . "tab/monitoring-last-alerts.php");
		exit;
	}
}

// Load classes

require_once $app_dir . '../vendor/autoload.php';

foreach (glob($app_dir . 'ui/*.php') as $file) {
    require_once $file;
}

require_once 'db.php';

foreach (glob($app_dir . 'model/*.php') as $file) {
    require_once $file;
}

foreach (glob($app_dir . 'app/*.php') as $file) {
    require_once $file;
}

// Start application

$app = new AdminDbApp();

?>
