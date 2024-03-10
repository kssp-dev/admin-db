<?php

$title = 'IP Addresses';

require_once __DIR__ . '/../app.php';

$model = new Ip($app->db);
$model->setOrder('ip', 'asc');
$crud = \Atk4\Ui\Crud::addTo($app, ['ipp' => 14]);
$crud->editFields = ['primary_ip'];
$crud->setModel($model);
$crud->sortable=true;
$crud->addQuickSearch(['ip', 'primary_ip'], true);

?>
