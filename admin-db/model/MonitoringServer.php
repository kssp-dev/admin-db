<?php

class MonitoringServer extends Atk4\Data\Model {
    public $table = 'monitoring.servers';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Server';

        $this->addFields([
			  "run_count" => ['required' => true, 'type' => 'integer', 'readOnly' => true],
			  "name" => ['required' => true]
        ]);
        
        $this->getField('id')->neverSave = true;	
    }
}

?>
