<?php

class PrimaryIp extends \Atk4\Data\Model {
    public $table = 'primary_ip';

    protected function init(): void
    {
        parent::init();
        
        $this->removeField('id');
        $this->idField = 'ip';

        $this->addFields([
			  'ip' => ['readOnly' => true]
        ]);
		
		$this -> hasMany('Ip', ['model' => [Ip::class]]);		
		$this -> hasMany('Scripts', ['model' => [Script::class]]);
    }
}

?>
