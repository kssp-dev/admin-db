<?php

$title = 'Monitoring Metrics';

require_once __DIR__ . '/../app.php';

$model = new MonitoringLastMetric($app->db);
$model->setOrder(['time']);

$grid = \Atk4\Ui\Grid::addTo($app, ['ipp' => 14]);

$grid->setModel($model, ['time', 'value', 'text_id', 'name', 'description']);

?>
