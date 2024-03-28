<?php

$title = 'IP Addresses';

require_once __DIR__ . '/../app.php';

$model = new Ip($app->db);
$model->setOrder('ip', 'asc');

$model->getUserAction('delete')->confirmation = true;

$crud = \Atk4\Ui\Crud::addTo($app, ['ipp' => 14]);
$crud->editFields = ['primary_ip'];

$crud->setModel($model);
$crud->sortable=true;
$crud->addQuickSearch(['ip', 'primary_ip'], true);

// Export

$exportModel = new Export($app->db);
$exportModel->addCondition('from', 'ip');

$fromModel = new PrimaryIp($app->db);
$fromModel->setOrder('ip', 'asc');

foreach ($exportModel as $id => $entity) {
	$icon = empty($entity->get('icon')) ? null : $entity->get('icon');
	
	$crud->menu->addItem([
		'Export to ' . $entity->get('to')
		, 'icon' => $icon
	])->on('click'
		, new ModalExporter($fromModel, $entity)
	);
}

?>
