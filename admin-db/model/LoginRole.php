<?php

class LoginRole extends \Atk4\Login\Model\Role {

    protected function init(): void
    {
        $this->table = 'login.roles';
		
        parent::init();        
    }
}

?>
