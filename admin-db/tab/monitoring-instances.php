<?php

$title = 'Monitoring Instances';

require_once __DIR__ . '/../app.php';


if (! $features['monitoring']) {
	$app->redirect($app_uri . "..");
	exit;
}


$model = new MonitoringInstance($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;
$model->getUserAction('delete')->enabled = static function (\Atk4\Data\Model $entity) {
		return $entity->countScripts() === 0;
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
			'displayFields' => ['id', 'enabled', 'name', 'instance', 'script_timeout', 'duration']
			, 'addFields' => ['enabled', 'name', 'instance', 'script_timeout']
			, 'editFields' => ['enabled', 'name', 'instance', 'script_timeout']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'instance', 'name']
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
