<?php

require_once 'EmptyNullModel.php';

class MonitoringSeries extends EmptyNullModel {
    public $table = 'monitoring.series';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Series';

        $this->addFields([
			  'time' => ['type' => 'datetime', 'neverSave' => true],
			  'text_id' => ['neverSave' => true],
			  'is_alert' => ['type' => 'boolean', 'neverSave' => true],
			  'name' => ['neverSave' => true],
			  'short_name' => ['neverSave' => true],
			  'value' => ['type' => 'integer', 'neverSave' => true],
			  'description' => ['type' => 'text', 'neverSave' => true]
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('target_id', ['required' => true, 'model' => new MonitoringTarget($this->getPersistence())]);
    }
}

?>
