<?php

require_once 'EmptyNullModel.php';

class MonitoringSeries extends EmptyNullModel {
    public $table = 'monitoring.series';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Series';

        $this->addFields([
			'time' => [
				'type' => 'datetime',
				'neverSave' => true
			],
			'name' => [
				'neverSave' => true
			],
			'uid' => [
				'neverSave' => true
			],
			'short_name' => [
				'neverSave' => true
			],
			'value' => [
				'type' => 'integer',
				'neverSave' => true
			],
			'description' => [
				'type' => 'text',
				'neverSave' => true
			],			  
			'repetition' => [
				'type' => 'integer',
				'nullable' => false
			],
			'notified' => [
				'type' => 'boolean',
				'nullable' => false
			]
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('target_id', [
			'model' => new MonitoringTarget($this->getPersistence())
		]);
		
		$this->hasOne('type_id', [
			'required' => true,
			'model' => new MonitoringType($this->getPersistence())
		])->addFields([
			'is_alert'
		]);
    }
}

?>
