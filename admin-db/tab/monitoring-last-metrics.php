<?php

$title = 'Monitoring Metrics';

require_once __DIR__ . '/../app.php';

$model = new MonitoringLastMetric($app->db);
$model->setOrder(['time']);

$gruid = \Atk4\Ui\Grid::addTo($app, ['ipp' => 14]);

$gruid->setModel($model, ['time', 'value', 'uid', 'name', 'description']);
$gruid->addQuickSearch(['uid', 'name', 'description']);

?>
