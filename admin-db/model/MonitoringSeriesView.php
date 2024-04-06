<?php

class MonitoringSeriesView extends Atk4\Data\Model {
    protected function init(): void
    {
        parent::init();
        
		$this->onHook(Atk4\Data\Model::HOOK_AFTER_DELETE, function (Atk4\Data\Model $entity) {
			global $app;
			
			$delete = $app->db->initQuery(new MonitoringSeries($app->db));
			$delete->mode('delete');
			$delete->where('uid', $entity->get('uid'));
            $delete->executeStatement();
		});
    }
}

?>
