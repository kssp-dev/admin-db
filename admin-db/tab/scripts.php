<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';

$model = new Script($app->db);
$model->setOrder('updated', 'asc');

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
];
				
$export_model = new Export($app->db);
$export_model->addCondition('from', 'scripts');

foreach ($export_model as $id => $entity) {
	$crud_options['menuActions']['Export to ' . $entity->get('to')] = [
		'icon' => empty($entity->get('icon')) ? null : $entity->get('icon'),
		'action' => new ModalExporter(new Script($app->db), $entity)
	];
}

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);
$crud->setModel($model, $crud_options);

?>
