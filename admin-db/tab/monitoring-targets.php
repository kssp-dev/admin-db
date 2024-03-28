<?php

$title = 'Monitoring Targets';

require_once __DIR__ . '/../app.php';

$model = new MonitoringTarget($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['enabled', 'name', 'text_id', 'period', 'target']
			, 'addFields' => ['enabled', 'script_id', 'name', 'text_id', 'period', 'target', 'script_data']
			, 'editFields' => ['enabled', 'script_id', 'name', 'text_id', 'period', 'target', 'script_data']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'name'
		, 'text_id'
		, 'target'
	]
	, 'menuActions' => []
	, 'columnActions' => [
		'Monitoring Test' => [
			'icon' => 'running',
			'caption' => 'Test',
			'ui' => 'basic green button',
			'action' => function ($p, $from_entity) {
				new ModalMonitoringTest($from_entity, $p);
			}
		]
	]
];

$crud->setModel($model, $crudOptions);

?>
