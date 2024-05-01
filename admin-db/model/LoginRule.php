<?php

class LoginRule extends \Atk4\Login\Model\AccessRule {

    protected function init(): void
    {
        $this->table = 'login.rules';
        $this->roleModelSeed = [LoginRole::class];
		
        parent::init();        
    }
}

?>
