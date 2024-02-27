<?php

$title = 'Administration';

require_once __DIR__ . '/../app.php';


Atk4\Ui\Button::addTo($app, [
	'Download database initialization script'
	, 'icon' => 'database'
])	->addClass('big blue button')
	->on('click', $app->jsRedirect($app_url . '../create.sql', false));

Atk4\Ui\View::addTo($app, ['ui' => 'hidden divider']);

Atk4\Ui\Button::addTo($app, [
	'Update the site'
	, 'icon' => 'download'
])	->addClass('big red button')
	->on('click', $app->jsRedirect($app_url . '../update', false));

?>
