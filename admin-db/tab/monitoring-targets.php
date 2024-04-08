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
			'displayFields' => ['id', 'enabled', 'name', 'uid', 'period', 'target']
			, 'addFields' => ['enabled', 'script_id', 'name', 'uid', 'period', 'target', 'script_data']
			, 'editFields' => ['enabled', 'name', 'period', 'target', 'script_data']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'name', 'uid', 'target']
	, 'menuActions' => []
	, 'columnActions' => [
		'Clone Target' => [
			'icon' => 'clone outline',
			'action' => function ($p, $entity, $crud) {
				new ModalCloner($entity, $p, $crud, ['name', 'uid', 'target']);
			}
		]
		, 'Monitoring Test' => [
			'icon' => 'running',
			'caption' => 'Test',
			'ui' => 'basic green button',
			'action' => function ($p, $entity) {
				new ModalMonitoringTest($entity, $p);
			}
		]
		, 'Remove Series' => [
			'icon' => 'trash',
			'caption' => 'Series',
			'ui' => 'basic orange button',
			'action' => function ($p, $entity) {
				new ModalMonitoringDeleteSeries($entity, $p);
			}
		]
	]
];

$crud->setModel($model, $crudOptions);

?>
