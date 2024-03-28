<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';

$model = new Script($app->db);
$model->setOrder('updated', 'asc');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['name', 'updated', 'logic']
			, 'addFields' => [
				'name'
				, 'script_ip'
				, 'script_file'
				, 'script_path'
				, 'timer_file'
				, 'logic'
				, 'database_ip'
				, 'database_name'
				, 'database_table'
				, 'description'
			]
			, 'editFields' => [
				'name'
				, 'script_ip'
				, 'script_file'
				, 'script_path'
				, 'timer_file'
				, 'logic'
				, 'database_ip'
				, 'database_name'
				, 'database_table'
				, 'description'
			]
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => [
		'name',
		'logic',
		'script_file',
		'script_path',
		'description',
		'script_ip',
		'timer_file',
		'database_ip',
		'database_name',
		'database_table'
	]
	, 'menuActions' => []
	, 'columnActions' => []
];

// Export

$exportModel = new Export($app->db);
$exportModel->addCondition('from', 'scripts');

$fromModel = new Script($app->db);
$fromModel->setOrder('name', 'asc');

foreach ($exportModel as $id => $entity) {
	$icon = empty($entity->get('icon')) ? null : $entity->get('icon');
	
	$crudOptions['menuActions']['Export to ' . $entity->get('to')] = [
		'icon' => $icon,
		'action' => new ModalExporter($fromModel, $entity)
	];
	
	if (!empty($entity->get('details'))) {
		$crudOptions['columnActions']['Export to ' . $entity->get('to')] = [
			'icon' => $icon,
			'caption' => isset($icon) ? null : $entity->get('to'),
			'action' => function ($p, $from_entity) use ($entity) {
				new ModalExporter($from_entity, $entity, $p);
			}
		];
	}
}

$crud->setModel($model, $crudOptions);

?>
