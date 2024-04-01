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
			'displayFields' => ['enabled', 'name', 'text_id', 'updated']
			, 'addFields' => ['enabled', 'server_id', 'name', 'text_id', 'script']
			, 'editFields' => ['enabled', 'server_id', 'name', 'text_id', 'script']
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

$crud->setModel($model, $crudOptions);

?>