<?php

require_once 'EmptyNullModel.php';

class MonitoringTarget extends EmptyNullModel {
    public $table = 'monitoring.targets';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Target';

        $this->addFields([
			  'enabled' => ['type' => 'boolean', 'nullable' => false],
			  'name' => ['required' => true],
			  'uid' => ['required' => true],
			  'period' => ['type' => 'integer', 'required' => true],
			  'target' => ['nullable' => false],
			  'script_data' => ['type' => 'text']
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('script_id', [
			'required' => true,
			'model' => new MonitoringScript($this->getPersistence())
		])->addFields([
			'script_uid' => 'uid'
		]);
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique name'];
			}
			
			if (preg_match('/^[^@#\s]+$/', $this->get('uid')) != 1) {
				return ['uid' => '@, # or blank forbidden'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('uid', $this->get('uid'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['uid' => 'Must have unique text id'];
			}
			
			if ($this->get('period') <= 0) {
				return ['period' => 'Must be a positive integer'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('target', $this->get('target'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['target' => 'Must have unique target'];
			}
		});	
    }
}

?>
