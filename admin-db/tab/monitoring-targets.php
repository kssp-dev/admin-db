<?php

$title = 'Monitoring Targets';

require_once __DIR__ . '/../app.php';


if (! $features['monitoring']) {
	$app->redirect($app_uri . "..");
	exit;
}


$model = new MonitoringTarget($app->db);
$model->setOrder('name');

$action = $model->getUserAction('delete');
$action->preview = static function (\Atk4\Data\Model $entity) {
		$entity->assertIsEntity();
				
		return '<span class="ui large red text">'
			. 'You are about to delete:<br><br>'
			. $entity->countSeries() . ' series rows<br>'
			. $entity->countLogs() . ' log rows'
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
			'displayFields' => ['id', 'enabled', 'name', 'script_uid', 'uid', 'period', 'target', 'duration']
			, 'addFields' => ['enabled', 'script_id', 'name', 'uid', 'period', 'target', 'script_data']
			, 'editFields' => ['enabled', 'script_id', 'name', 'uid', 'period', 'target', 'script_data']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'name', 'uid', 'script_uid', 'target']
	, 'menuActions' => []
	, 'columnActions' => [
		'Clone Target' => [
			'icon' => 'clone outline',
			'disabled' => ! $app->auth->user->isLoaded(),
			'modal' => function ($p, $entity, $crud) {
				new ModalCloner($entity, $p, $crud, ['name', 'uid', 'target', 'script_id']);
			}
		]
		, 'Monitoring Test' => [
			'icon' => 'running',
			'caption' => 'Test',
			'ui' => 'basic green button',
			'modal' => function ($p, $entity, $crud) {
				new ModalMonitoringTest($entity, $p, $crud);
			}
		]
		, 'Remove Series' => [
			'icon' => 'trash',
			'caption' => 'Series',
			'ui' => 'basic orange button',
			'confirmation' => 'Are you sure to delete all series of the target?',
			'disabled' => ! $app->auth->user->isLoaded(),
			'action' => function (\Atk4\Data\Model $entity) {
				return new \Atk4\Ui\Js\JsToast($entity->deleteSeries());
			}
		]
	]
];

$crud->setModel($model, $crudOptions);

?>
