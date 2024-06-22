<?php

require_once 'ModalLoader.php';

class MonitoringTargetTest extends ModalLoader {

    function __construct(\Atk4\Data\Model $targetModel, \Atk4\Ui\View $virtualPage, \Atk4\Ui\View $table) {
        parent::__construct(
			'Monitoring Test'
			, function (LoaderEx $p) use ($targetModel, $table) {
				$targetModel->assertIsEntity();

				global $app, $app_dir;
				global $db_host, $db_port, $db_name, $db_user, $db_psw;

				$logModel = new MonitoringLog($app->db);
				$logModel->setOrder(['time' => 'desc']);
				$logModel->addCondition('target_id', $targetModel->get('id'));
				$logEntity = $logModel->tryLoadAny();

				$seriesModel = new MonitoringSeries($app->db);
				$seriesModel->setOrder(['time' => 'desc']);
				$seriesModel->addCondition('target_id', $targetModel->get('id'));
				$seriesEntity = $seriesModel->tryLoadAny();


				$environment = [
					'ADMIN_DB_HOST' => $db_host
					, 'ADMIN_DB_PORT' => $db_port
					, 'ADMIN_DB_NAME' => $db_name
					, 'ADMIN_DB_USER' => $db_user
					, 'ADMIN_DB_PSW' => $db_psw
					, 'MONITORING_USER' => getenv('MONITORING_USER')
					, 'script_id' => $targetModel->get('script_id')
					, 'target_ids' => $targetModel->get('id')
					, 'call_function' => 'func_run_targets'
					, 'temp_dir' => ''
				];

				$param='';
				foreach ($environment as $key=>$var) {
					$param = $param . $key . '=' . $var . "\t";
				}

				$cmd = 'bash "' . __DIR__  . '/../tool/monitoring-launcher.sh" "' . $param . '" 2>&1';
				//print_r($cmd . "\n");

				$startTime = microtime(true);

				$output = [];
				$code = 0;
				$res = exec($cmd, $output, $code);

				$duration = (int) ceil((microtime(true) - $startTime) * 1000);

				$p->addHeader($targetModel->get('script_name') . ' [ ' . $targetModel->get('name') . ' ]', 3);

				if ($seriesEntity && $seriesEntity->isEntity()) {
					$seriesModel->addCondition('time', '>', $seriesEntity->get('time'));
				}

				$p->addHeader($seriesModel->executeCountQuery() . ' metrics added, duration ' . $duration . ' ms', 4);
				$p->addGrid($seriesModel, ['ipp' => 10], ['is_alert', 'value', 'uid', 'name', 'description']);


				if ($logEntity && $logEntity->isEntity()) {
					$logModel->addCondition('time', '>', $logEntity->get('time'));
				}

				$logEntity = $logModel->tryLoadAny();

				if ($logEntity && $logEntity->isEntity()) {
					$p->addHeader('Script Log', 4);
					$p->addTextarea($logEntity->get('output'))
						->setInputAttr('style', 'font-family: monospace;');
				}


				$p->addHeader('Launcher Log', 4);
				$p->addTextarea($output)
					->setInputAttr('style', 'font-family: monospace;');

				$this->addCloseButton($p);
				$this->addRedirectButton($p, 'monitoring-scripts', 'Scripts');

				$table->jsReload();
			}
			, $virtualPage
		);
    }

}

?>
