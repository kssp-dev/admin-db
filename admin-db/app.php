<?php

$app_dir = __DIR__ . '/';

require_once $app_dir . '../vendor/autoload.php';

foreach (glob($app_dir . 'mastercrud/*.php') as $file) {
    require_once $file;
}

$app_uri = substr_replace($app_dir, '', 0, strlen($_SERVER['DOCUMENT_ROOT']));

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
