<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';

$model = new Script($app->db);
$model->setOrder('updated', 'asc');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crud_options = [
	[
		'_crud' => [
			'displayFields' => ['name', 'updated', 'logic']
			, 'addFields' => [
				"name"
				, "script_ip"
				, "script_file"
				, "script_path"
				, "timer_file"
				, "logic"
				, "database_ip"
				, "database_name"
				, "database_table"
				, "description"
			]
			, 'editFields' => [
				"name"
				, "script_ip"
				, "script_file"
				, "script_path"
				, "timer_file"
				, "logic"
				, "database_ip"
				, "database_name"
				, "database_table"
				, "description"
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

$export_model = new Export($app->db);
$export_model->addCondition('from', 'scripts');

$from_model = new Script($app->db);
$from_model->setOrder('name', 'asc');

foreach ($export_model as $id => $entity) {
	$crud_options['menuActions']['Export to ' . $entity->get('to')] = [
		'icon' => empty($entity->get('icon')) ? null : $entity->get('icon'),
		'action' => new ModalExporter($from_model, $entity)
	];
	
	if (!empty($entity->get('detales'))) {
		$crud_options['columnActions']['Export to ' . $entity->get('to')] = [
			'icon' => empty($entity->get('icon')) ? null : $entity->get('icon'),
			'action' => function ($p, $from_entity) use ($entity) {
				new ModalExporter($from_entity, $entity, $p);
			}
		];
	}
}

$crud->setModel($model, $crud_options);

//error_log(print_r("getUserAction " . print_r($model->getUserAction('delete'), true), true));

?>
