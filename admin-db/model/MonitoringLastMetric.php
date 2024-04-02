<?php

class MonitoringLastMetric extends Atk4\Data\Model {
    public $table = 'monitoring.last_metrics';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Last Metric';

        $this->addFields([
			  'time' => ['type' => 'datetime', 'neverSave' => true],
			  'value' => ['type' => 'integer', 'neverSave' => true],
			  'uid' => ['neverSave' => true],
			  'name' => ['neverSave' => true],
			  'short_name' => ['neverSave' => true],
			  'description' => ['type' => 'text', 'neverSave' => true]
        ]);
    }
}

?>
