<?php

$title = 'Monitoring Targets';

require_once __DIR__ . '/../app.php';

$model = new MonitoringTarget($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crud_options = [
	[
		'_crud' => [
			'displayFields' => ['script_id', 'name', 'text_id', 'period', 'target']
			, 'addFields' => [
				"script_id"
				, "name"
				, "text_id"
				, "period"
				, "target"
			]
			, 'editFields' => [
				"script_id"
				, "name"
				, "text_id"
				, "period"
				, "target"
			]
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'name'
		, 'text_id'
		, 'target'
	]
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crud_options);

?>
