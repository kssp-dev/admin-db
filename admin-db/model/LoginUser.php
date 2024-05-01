<?php

class LoginUser extends \Atk4\Login\Model\User {

    protected function init(): void
    {
        $this->table = 'login.users';
        $this->roleModelSeed = [LoginRole::class];
		
        parent::init();
        
        $this->addField('login', ['required' => true]);
        
        $this->getField('email')->caption = null;
        $this->getField('email')->required = false;
        $this->getField('role_id')->system = true;
        
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_SAVE, function (\Atk4\Data\Model $m) {
			if (empty($m->get('email'))) {
				$m->set('email', null);
			}
		});
    }
    
}

?>
