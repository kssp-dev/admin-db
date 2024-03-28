<?php

$title = 'Monitoring Servers';

require_once __DIR__ . '/../app.php';

$model = new MonitoringServer($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['enabled', 'name', 'run_count']
			, 'addFields' => ['enabled', 'name']
			, 'editFields' => ['enabled', 'name']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'name'
	]
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
