<?php

class MonitoringLastAlert extends Atk4\Data\Model {
    public $table = 'monitoring.last_alerts';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Last Alert';

        $this->addFields([
			  'time' => ['type' => 'datetime', 'neverSave' => true],
			  'value' => ['type' => 'integer', 'neverSave' => true],
			  'repetition' => ['type' => 'integer', 'neverSave' => true],
			  'uid' => ['neverSave' => true],
			  'name' => ['neverSave' => true],
			  'short_name' => ['neverSave' => true],
			  'description' => ['type' => 'text', 'neverSave' => true]
        ]);
        
		$this->onHook(Atk4\Data\Model::HOOK_BEFORE_DELETE, function (Atk4\Data\Model $entity) {
			global $app;

			$series = new MonitoringSeries($app->db);
			$series->addCondition('uid', $entity->get('uid'));
			
			foreach ($series as $id => $entity) {
				$entity->delete();
			}

			$entity->hook(Atk4\Data\Model::HOOK_AFTER_DELETE);
			$entity->breakHook(true); // this will cancel original delete()
		});
    }
}

?>
