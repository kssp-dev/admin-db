<?php

$title = 'Monitoring Alerts';

require_once __DIR__ . '/../app.php';

$model = new MonitoringAlerts($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crud_options = [
	[
		'_crud' => [
			'displayFields' => ['script_id', 'name', 'code', 'description']
			, 'addFields' => [
				"script_id"
				, "name"
				, "code"
				, "description"
			]
			, 'editFields' => [
				"script_id"
				, "name"
				, "code"
				, "description"
			]
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'name'
		, 'code'
		, 'description'
	]
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crud_options);

?>
