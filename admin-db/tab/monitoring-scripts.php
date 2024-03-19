<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';

$model = new MonitoringScript($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crud_options = [
	[
		'_crud' => [
			'displayFields' => ['name', 'updated', 'text_id']
			, 'addFields' => [
				"name"
				, "text_id"
				, "script"
				, "server_id"
			]
			, 'editFields' => [
				"name"
				, "text_id"
				, "script"
				, "server_id"
			]
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'name'
		, 'text_id'
	]
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crud_options);

?>
