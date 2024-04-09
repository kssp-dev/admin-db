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
			'displayFields' => ['id', 'enabled', 'name', 'instance', 'uid', 'updated']
			, 'addFields' => ['enabled', 'instance_id', 'name', 'uid']
			, 'editFields' => ['enabled', 'instance_id', 'name']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'name', 'uid', 'instance']
	, 'menuActions' => []
	, 'columnActions' => [
		'Clone Script' => [
			'icon' => 'clone outline',
			'action' => function ($p, $entity, $crud) {
				new ModalCloner($entity, $p, $crud, ['name', 'uid']);
			}
		]
		, 'Script Editor' => [
			'icon' => 'file medical alternate',
			'caption' => 'Script',
			'ui' => 'basic blue button',
			'action' => function ($p, $entity) {
				new ScriptEditor($entity, $p);
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
