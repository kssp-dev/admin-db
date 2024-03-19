<?php

$title = 'Monitoring Servers';

require_once __DIR__ . '/../app.php';

$model = new MonitoringServer($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crud_options = [
	[
		'_crud' => [
			'displayFields' => ['name', 'run_count']
			, 'addFields' => [
				"name"
			]
			, 'editFields' => [
				"name"
			]
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'name'
	]
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crud_options);

?>
