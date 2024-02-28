<?php

class ScriptNameModel extends \Atk4\Data\Model {
    public $table = 'scripts';

    protected function init(): void
    {
        parent::init();
        
        $this->removeField('id');
        $this->idField = 'name';

        $this->addFields([
			  'name' => ['readOnly' => true],
			  'id' => ['readOnly' => true]
        ]);
    }
}

?>
