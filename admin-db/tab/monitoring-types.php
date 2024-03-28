<?php

$title = 'Monitoring Types';

require_once __DIR__ . '/../app.php';

$model = new MonitoringType($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['is_alert', 'name', 'text_id', 'description']
			, 'addFields' => ['is_alert', 'name', 'text_id', 'description']
			, 'editFields' => ['is_alert', 'name', 'text_id', 'description']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'name'
		, 'text_id'
		, 'description'
	]
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
