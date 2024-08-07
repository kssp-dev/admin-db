<?php

$title = 'Monitoring Metrics';

require_once __DIR__ . '/../app.php';


if (! $features['monitoring']) {
	$app->redirect($app_uri . "..");
	exit;
}


$model = new MonitoringLastMetric($app->db);
$model->setOrder(['time']);

$model->removeUserAction('add');
$model->removeUserAction('edit');

$model->getUserAction('delete')->confirmation = true;
		
if (! $app->auth->user->isLoaded()) {
	$model->getUserAction('delete')->enabled = false;
}

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
	, 'filterColumn' => true
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
