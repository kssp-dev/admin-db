<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';

$model = new Script($app->db);
$model->setOrder('name', 'asc');

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);
$crud->setModel($model, [
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
	, 'menuActions' => [
		'Export to Wiki' => function (Atk4\Ui\VirtualPage $vp, Script $model, string $caption) {
			\Atk4\Ui\Icon::addTo($vp, ['content' => 'drafting compass']);
			\Atk4\Ui\Text::addTo($vp, ['content' => 'Under constraction']);
		}
	]
]);
?>
