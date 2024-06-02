<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';


if (! $features['monitoring']) {
	$app->redirect($app_uri . "..");
	exit;
}


$model = new MonitoringScript($app->db);
$model->setOrder('name');

$action = $model->getUserAction('delete');
$action->preview = static function (\Atk4\Data\Model $entity) {
		$entity->assertIsEntity();
				
		return '<span class="ui large red text">'
			. 'You are about to delete:<br><br>'
			. $entity->countTargets() . ' targets<br>'
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
			'displayFields' => ['id', 'enabled', 'name', 'uid', 'duration', 'updated', 'login']
			, 'addFields' => ['name', 'uid']
			, 'editFields' => ['enabled', 'name', 'uid']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'name', 'uid', 'updated', 'login']
	, 'menuActions' => []
	, 'columnActions' => [
		'Clone Script' => [
			'icon' => 'clone outline',
			'disabled' => ! $app->auth->user->isLoaded(),
			'modal' => function ($p, $entity, $crud) {
				new ModalCloner($entity, $p, $crud, ['name', 'uid']);
			}
		]
		, 'Script Editor' => [
			'icon' => 'file medical alternate',
			'caption' => 'Script',
			'ui' => 'basic blue button',
			'disabled' => ! $app->auth->user->isLoaded(),
			'modal' => function ($p, $entity, $crud) {
				new ScriptEditor($entity, $p, $crud);
			}
		]
		, 'Remove Series' => [
			'icon' => 'trash',
			'caption' => 'Series',
			'ui' => 'basic orange button',
			'confirmation' => 'Are you sure to delete all series of the script?',
			'disabled' => ! $app->auth->user->isLoaded(),
			'action' => function (\Atk4\Data\Model $entity) {
				return new \Atk4\Ui\Js\JsToast($entity->deleteSeries());
			}
		]
	]
];

$crud->setModel($model, $crudOptions);

?>
