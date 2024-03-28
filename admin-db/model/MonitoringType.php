<?php

require_once 'EmptyNullModel.php';

class MonitoringType extends EmptyNullModel {
    public $table = 'monitoring.types';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Type';

        $this->addFields([
			  'is_alert' => ['type' => 'boolean', 'nullable' => false],
			  'name' => ['required' => true],
			  'text_id' => ['required' => true],
			  'description' => ['type' => 'text']
        ]);
        
        $this->getField('id')->neverSave = true;		
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if (preg_match('/[\'"]/', $this->get('description')) == 1) {
				return ['description' => 'Quotation mark forbidden'];
			}
			
			if (preg_match('/^[^@#\s]+$/', $this->get('text_id')) != 1) {
				return ['text_id' => '@, # or blank forbidden'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('text_id', $this->get('text_id'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['text_id' => 'Must have unique text id'];
			}
			
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
