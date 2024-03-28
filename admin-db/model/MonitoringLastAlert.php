<?php

class MonitoringLastAlert extends Atk4\Data\Model {
    public $table = 'monitoring.last_alerts';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Last Alert';

        $this->addFields([
			  'time' => ['type' => 'datetime', 'neverSave' => true],
			  'value' => ['type' => 'integer', 'neverSave' => true],
			  'text_id' => ['neverSave' => true],
			  'name' => ['neverSave' => true],
			  'short_name' => ['neverSave' => true],
			  'description' => ['type' => 'text', 'neverSave' => true]
        ]);
    }
}

?>
