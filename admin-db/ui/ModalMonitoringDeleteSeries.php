<?php

require_once 'ModalLoader.php';

class ModalMonitoringDeleteSeries extends ModalLoader {

    function __construct(Atk4\Data\Model $entity, Atk4\Ui\View $virtualPage = null) {
        parent::__construct(
			'Series Removing'
			, function (LoaderEx $p) use ($entity) {
				$entity->assertIsEntity();
				
				global $app;
				
				$count = 0;
				
				if ($entity->hasField('script_id')) {		// Target entity
					$count += $this->removeTargetSeries($entity);
				} else {									// Script entity
					$target = new MonitoringTarget($app->db);
					$target->addCondition('script_id', $entity->get('id'));
					
					foreach ($target as $id => $ent) {
						$count += $this->removeTargetSeries($ent);
					}
				}
							
				$p->addMessage('Series were successfully deleted'
					, $count . ' rows were deleted'
					, 'success'
				);
				
				$p->addCloseButton($app);
			}
			, $virtualPage
		);
    }
	
	function removeTargetSeries(Atk4\Data\Model $targetEntity) {
		global $app;
				
		$delete = $app->db->initQuery(new MonitoringSeries($app->db));
		$delete->mode('delete');
		$delete->where('target_id', $targetEntity->get('id'));
		return $delete->executeStatement();
	}
    
}

?>
