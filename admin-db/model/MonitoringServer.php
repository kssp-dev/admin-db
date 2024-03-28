<?php

class MonitoringServer extends Atk4\Data\Model {
    public $table = 'monitoring.servers';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Server';

        $this->addFields([
			  'enabled' => ['type' => 'boolean', 'nullable' => false],
			  'run_count' => ['required' => true, 'type' => 'integer', 'readOnly' => true],
			  'name' => ['required' => true]
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			$m = clone $this->getModel();
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique name'];
			}
		});
    }
}

?>
