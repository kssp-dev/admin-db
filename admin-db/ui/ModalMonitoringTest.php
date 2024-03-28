<?php

require_once 'ModalLoader.php';

class ModalMonitoringTest extends ModalLoader {

    function __construct(Atk4\Data\Model $targetModel, Atk4\Ui\View $virtualPage = null) {
        parent::__construct(
			'Monitoring Test'
			, function (LoaderEx $p) use ($targetModel) {
				if (!$targetModel->isEntity()) {
					return;
				}
				
				global $app, $app_dir;
				
				$logModel = new MonitoringLog($app->db);
				$logModel->setOrder(['time' => 'desc']);
				$logModel->addCondition('target_id', $targetModel->get('id'));
				$logEntity = $logModel->tryLoadAny();
				
				$seriesModel = new MonitoringSeries($app->db);
				$seriesModel->setOrder(['time' => 'desc']);
				$seriesModel->addCondition('target_id', $targetModel->get('id'));
				$seriesEntity = $seriesModel->tryLoadAny();
				
				
				$output = [];
				$code = 0;
				$res = exec(
					'php "' . $app_dir . 'tool/monitoring_test.php" "'
					. $targetModel->get('script_id')
					. '" "'
					. $targetModel->get('id')
					. '"'
				, $output, $code);
				
				
				$p->addHeader($targetModel->get('name'), 3);
				
				$p->addHeader('Metrics Added', 4);
				
				if ($seriesEntity && $seriesEntity->isEntity()) {
					$seriesModel = clone $seriesEntity->getModel();
					$seriesModel->addCondition('time', '>', $seriesEntity->get('time'));
				}
				
				$p->addGrid($seriesModel, ['ipp' => 10], ['is_alert', 'value', 'text_id', 'name']);			
								
				$p->addHeader('Script Log', 4);
					
				if ($logEntity && $logEntity->isEntity()) {
					$logModel = clone $logEntity->getModel();
					$logModel->addCondition('time', '>', $logEntity->get('time'));
				}
				
				$logEntity = $logModel->tryLoadAny();
				$log = '';
				
				if ($logEntity && $logEntity->isEntity()) {
					$log = $logEntity->get('output');
				}
					
				$p->addTextarea($log);
				
				$p->addHeader('Main Script Log', 4);
								
				$p->addTextarea($output);
				
				$p->addCloseButton($app);
			}
			, $virtualPage
		);
    }
    
}

?>
