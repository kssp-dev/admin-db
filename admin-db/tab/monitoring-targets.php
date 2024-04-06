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
			'displayFields' => ['enabled', 'name', 'uid', 'period', 'target']
			, 'addFields' => ['enabled', 'script_id', 'name', 'uid', 'period', 'target', 'script_data']
			, 'editFields' => ['enabled', 'name', 'period', 'target', 'script_data']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'name', 'uid', 'target']
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
