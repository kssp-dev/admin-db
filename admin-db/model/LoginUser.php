<?php

class LoginUser extends \Atk4\Login\Model\User {

    protected function init(): void
    {
        $this->table = 'login.users';
        $this->roleModelSeed = [LoginRole::class];
		
        parent::init();
    }
    
}

?>
