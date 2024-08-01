<?php

require_once 'EmptyNullModel.php';

class MonitoringLog extends EmptyNullModel {
    public $table = 'monitoring.log';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Log';

        $this->addFields([
			'time' => [
				'type' => 'datetime',
				'neverSave' => true
			],
			'code' => [
				'type' => 'integer',
				'neverSave' => true
			],
			'output' => [
				'type' => 'text',
				'neverSave' => true
			]
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('target_id', ['required' => true, 'model' => new MonitoringTarget($this->getPersistence())]);
    }
}

?>
