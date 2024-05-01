<?php

class LoginForm extends \Atk4\Login\Form\Login {

    #[\Override]
    protected function init(): void
    {
		$this->linkSuccess = [];
		$this->linkForgot = false;
		
        parent::init();
    }
    
}

?>
