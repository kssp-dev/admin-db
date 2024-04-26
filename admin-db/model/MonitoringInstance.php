<?php

class MonitoringInstance extends \Atk4\Data\Model {
    public $table = 'monitoring.instances';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Instance';

        $this->addFields([
			  'enabled' => ['type' => 'boolean', 'nullable' => false],
			  'instance' => ['required' => true],
			  'name' => ['required' => true]
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if (preg_match('/^[^\s]+$/', $this->get('instance')) != 1) {
				return ['instance' => 'Blank forbidden'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('instance', $this->get('instance'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique instance'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique name'];
			}
		});
    }
    
    public function countScripts() {
		$this->assertIsEntity();
		
		global $app;
		
		$model = new MonitoringScript($app->db);
		$model->addCondition('instance_id', $this->get('id'));
		$count = $model->executeCountQuery();
		
		return $count;
	}
}

?>
