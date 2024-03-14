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

$export_model = new Export($app->db);
$export_model->addCondition('from', 'ip');

$from_model = new PrimaryIp($app->db);
$from_model->setOrder('ip', 'asc');

foreach ($export_model as $id => $entity) {
	$crud->menu->addItem([
		'Export to ' . $entity->get('to')
		, 'icon' => empty($entity->get('icon')) ? null : $entity->get('icon')
	])->on('click'
		, new ModalExporter($from_model, $entity)
	);
}

?>
