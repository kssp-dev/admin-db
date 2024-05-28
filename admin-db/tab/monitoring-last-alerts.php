<?php

$title = 'Monitoring Alerts';

require_once __DIR__ . '/../app.php';


if (! $features['monitoring']) {
	$app->redirect($app_uri . "..");
	exit;
}


$model = new MonitoringLastAlert($app->db);
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
	, 'menuActions' => []
	, 'columnActions' => [
		'Notification Test' => [
			'icon' => 'bell outline',
			'caption' => 'Notify',
			'ui' => 'basic green button',
			'modal' => function ($p, $entity, $crud) {
				new MonitoringNotificationTest($entity, $p, $crud);
			}
		]
	]
];

$crud->setModel($model, $crudOptions);

?>
