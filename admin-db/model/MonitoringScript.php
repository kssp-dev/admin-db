<?php

class MonitoringScript extends Atk4\Data\Model {
    public $table = 'monitoring.scripts';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Script';

        $this->addFields([
			  "name" => ['required' => true],
			  "text_id" => ['required' => true],
			  "script" => ['required' => true, 'type' => 'text'],
			  "updated" => ['type' => 'date']
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('server_id', ['required' => true, 'model' => new MonitoringServer($this->getPersistence())]);
        
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_SAVE, function (\Atk4\Data\Model $m) {
			$m->set('updated', new DateTime());
		});
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if (preg_match('/^[^@\s]+$/', $this->get('text_id')) != 1) {
				return ['text_id' => 'At or blank forbidden'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('text_id', $this->get('text_id'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['text_id' => 'Script of the text id already exists'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Script of the name already exists'];
			}
		});		
    }
}

?>
