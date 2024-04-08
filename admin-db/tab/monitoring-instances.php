<?php

$title = 'Monitoring Instances';

require_once __DIR__ . '/../app.php';

$model = new MonitoringInstance($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['id', 'enabled', 'instance', 'name', 'run_count']
			, 'addFields' => ['enabled', 'instance', 'name']
			, 'editFields' => ['enabled', 'instance', 'name']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'instance', 'name']
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
