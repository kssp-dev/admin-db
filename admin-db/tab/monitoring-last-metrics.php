<?php

$title = 'Monitoring Metrics';

require_once __DIR__ . '/../app.php';

$model = new MonitoringLastMetric($app->db);
$model->setOrder(['time']);

$model->removeUserAction('add');
$model->removeUserAction('edit');

$model->getUserAction('delete')->confirmation = true;

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
