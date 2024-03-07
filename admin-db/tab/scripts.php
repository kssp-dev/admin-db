<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';

$model = new Script($app->db);
$model->setOrder('name', 'asc');

$crud_options = [
	[
		'_crud' => [
			'displayFields' => ['name', 'logic']
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
	$crud_options['menuActions']['Export to ' . $entity->get('to')] = function (Atk4\Ui\VirtualPage $vp, Script $model, string $caption) {
		\Atk4\Ui\Icon::addTo($vp, ['content' => 'drafting compass']);
		\Atk4\Ui\Text::addTo($vp, ['content' => 'Under constraction']);
	};
}

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);
$crud->setModel($model, $crud_options);

?>
