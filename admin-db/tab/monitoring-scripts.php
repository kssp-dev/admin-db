<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';

$model = new MonitoringScript($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['enabled', 'name', 'uid', 'updated']
			, 'addFields' => ['enabled', 'instance_id', 'name', 'uid', 'script']
			, 'editFields' => ['enabled', 'instance_id', 'name', 'uid', 'script']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'name', 'uid']
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
