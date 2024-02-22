<?php

class ScriptFile extends \Atk4\Data\Model {
    public $table = 'scripts';

    protected function init(): void
    {
        parent::init();
        
        $this->removeField('id');
        $this->idField = 'script_file';

        $this->addFields([
			  'script_file' => ['readOnly' => true],
			  'id' => ['readOnly' => true]
        ]);
    }
}

?>
