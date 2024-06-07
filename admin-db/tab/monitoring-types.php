<?php

$title = 'Monitoring Types';

require_once __DIR__ . '/../app.php';


if (! $features['monitoring']) {
	$app->redirect($app_uri . "..");
	exit;
}


$model = new MonitoringType($app->db);
$model->setOrder('name');

$action = $model->getUserAction('delete');
$action->preview = static function (\Atk4\Data\Model $entity) {
		$entity->assertIsEntity();
				
		return '<span class="ui large red text">'
			. 'You are about to delete:<br><br>'
			. $entity->countSeries() . ' series rows'
			. '</span>';
	};
		
if (! $app->auth->user->isLoaded()) {
	$model->getUserAction('add')->enabled = false;
	$model->getUserAction('edit')->enabled = false;
	$model->getUserAction('delete')->enabled = false;
}

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['is_alert', 'name', 'uid', 'notification_delay', 'notification_period', 'description']
			, 'addFields' => ['is_alert', 'name', 'uid', 'notification_delay', 'notification_period', 'description']
			, 'editFields' => ['is_alert', 'name', 'uid', 'notification_delay', 'notification_period', 'description']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['name', 'uid', 'description']
	, 'menuActions' => []
	, 'columnActions' => [
		'Remove Series' => [
			'icon' => 'trash',
			'caption' => 'Series',
			'ui' => 'basic orange button',
			'confirmation' => 'Are you sure to delete all series of the type?',
			'disabled' => ! $app->auth->user->isLoaded(),
			'action' => function (\Atk4\Data\Model $entity) {
				return new \Atk4\Ui\Js\JsToast($entity->deleteSeries());
			}
		]
	]
];

$crud->setModel($model, $crudOptions);

?>
