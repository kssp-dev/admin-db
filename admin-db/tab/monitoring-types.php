<?php

$title = 'Monitoring Types';

require_once __DIR__ . '/../app.php';

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

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['is_alert', 'name', 'uid', 'description']
			, 'addFields' => ['is_alert', 'name', 'uid', 'notification_delay', 'notification_period', 'description']
			, 'editFields' => ['is_alert', 'name', 'uid', 'notification_delay', 'notification_period', 'description']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['name', 'uid', 'description']
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
