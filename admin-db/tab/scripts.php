<?php

$title = 'Monitoring Scripts';

require_once __DIR__ . '/../app.php';

$model = new Script($app->db);
$model->setOrder('name', 'asc');
$crud = \Atk4\Ui\Crud::addTo($app);
$crud->displayFields = ['name', 'logic'];
$crud->setModel($model);
$crud->sortable=true;
$crud->addQuickSearch(['name', 'logic', 'script_file', 'description', 'script_ip', 'database_ip'], true);

?>
