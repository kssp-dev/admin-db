<?php

class MonitoringTarget extends Atk4\Data\Model {
    public $table = 'monitoring.targets';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Target';

        $this->addFields([
			  "name" => ['required' => true],
			  "text_id" => ['required' => true],
			  "target" => ['required' => true],
			  "period" => ['required' => true, 'type' => 'integer']
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('script_id', ['required' => true, 'model' => new MonitoringScript($this->getPersistence())]);
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if ($this->get('period') <= 0) {
				return ['period' => 'Must be a positive integer'];
			}
			
			if (preg_match('/^[^@\s]+$/', $this->get('text_id')) != 1) {
				return ['text_id' => 'At or blank forbidden'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('text_id', $this->get('text_id'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['text_id' => 'Target of the text id already exists'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Target of the name already exists'];
			}
		});		
    }
}

?>
