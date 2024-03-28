<?php

$title = 'Export Templates';

require_once __DIR__ . '/../app.php';

$model = new Export($app->db);
$model->setOrder('from', 'asc');

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);
$crud->setModel($model, [
	[
		'_crud' => [
			'displayFields' => ['from', 'to']
			, 'editFields' => ['icon', 'header', 'row', 'footer', 'details', 'link']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'from'
		, 'to'
	]
]);


?>
