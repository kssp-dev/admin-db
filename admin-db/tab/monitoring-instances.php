<?php

$title = 'Monitoring Instances';

require_once __DIR__ . '/../app.php';

$model = new MonitoringInstance($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;
$model->getUserAction('delete')->enabled = static function (\Atk4\Data\Model $entity) {
		return $entity->countScripts() === 0;
	};

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['id', 'enabled', 'name', 'instance']
			, 'addFields' => ['enabled', 'name', 'instance']
			, 'editFields' => ['enabled', 'name', 'instance']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['id', 'instance', 'name']
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
