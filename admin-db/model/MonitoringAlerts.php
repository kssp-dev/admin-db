<?php

require_once 'EmptyNullModel.php';

class MonitoringAlerts extends EmptyNullModel {
    public $table = 'monitoring.alerts';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Alert';

        $this->addFields([
			  "name" => ['required' => true],
			  "code" => ['required' => true, 'type' => 'integer'],
			  "description" => ['type' => 'text']
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('script_id', ['required' => true, 'model' => new MonitoringScript($this->getPersistence())]);
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if ($this->get('code') <= 0) {
				return ['code' => 'Must be a positive integer'];
			}
			if (preg_match('/[\'"]/', $this->get('description')) == 1) {
				return ['description' => 'Quotation mark forbidden'];
			}			
			
			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('code', $this->get('code'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['code' => 'Alert of the code already exists'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Alert of the name already exists'];
			}
		});		
    }
}

?>
