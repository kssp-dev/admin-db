<?php

require_once 'ModalLoader.php';

class MonitoringNotificationTest extends ModalLoader {

    function __construct(\Atk4\Data\Model $alertModel, \Atk4\Ui\View $virtualPage, \Atk4\Ui\View $table) {
        parent::__construct(
			'Notification Test'
			, function (LoaderEx $p) use ($alertModel, $table) {
				$alertModel->assertIsEntity();
				
				global $app;//, $app_dir;
				global $db_host, $db_port, $db_name, $db_user, $db_psw;
				
				$seriesModel = new MonitoringSeries($app->db);
				$seriesEntity = $seriesModel->load($alertModel->get('id'));
				
				$typeModel = new MonitoringType($app->db);
				$typeEntity = $typeModel->load($seriesEntity->get('type_id'));				
				
				$seriesEntity->set('notified', FALSE);
				$seriesEntity->set('repetition', $typeEntity->get('notification_delay'));
				$seriesEntity->save();
				
				
				$environment = [
					'ADMIN_DB_HOST' => $db_host
					, 'ADMIN_DB_PORT' => $db_port
					, 'ADMIN_DB_NAME' => $db_name
					, 'ADMIN_DB_USER' => $db_user
					, 'ADMIN_DB_PSW' => $db_psw
					, 'call_function' => 'func_notify'
				];

				$param='';
				foreach ($environment as $key=>$var) {
					$param = $param . $key . '=' . $var . "\t";
				}

				$cmd = 'bash "' . __DIR__  . '/../tool/monitoring-launcher.sh" "' . $param . '" 2>&1';
				//print_r($cmd . "\n");
				
				$output = [];
				$code = 0;
				$res = exec($cmd, $output, $code);
				
				
				$p->addHeader($alertModel->get('name'), 3);
					
									
				$p->addTextarea($output, 25)
					->setInputAttr('style', 'font-family: monospace;');
				
				$p->addCloseButton($app);
				
				$table->jsReload();
			}
			, $virtualPage
		);
    }
    
}

?>
