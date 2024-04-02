<?php

$title = 'Monitoring Alerts';

require_once __DIR__ . '/../app.php';

$model = new MonitoringLastAlert($app->db);
$model->setOrder(['time']);

$model->removeUserAction('add');
$model->removeUserAction('edit');
$model->removeUserAction('delete');

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['time', 'value', 'uid', 'name']
			, 'addFields' => []
			, 'editFields' => []
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['uid', 'name', 'description']
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
