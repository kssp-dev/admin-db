<?php

class Ip extends EmptyNullModel {
    public $table = 'ip';

    protected function init(): void
    {
        parent::init();
        
        $this->removeField('id');
        $this->idField = 'ip';

        $this->addFields([
			  "ip" => ['required' => true],
			  "primary_ip"
        ]);
        
		$this->hasOne('PrimaryIp', ['model' => new PrimaryIp($this->getPersistence()), 'ourField' => 'primary_ip', 'theirField' => 'ip']);
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->get('ip')) != 1) {
				return ['ip' => 'IP address required'];
			}
			
			if (!empty($this->get('primary_ip')) && preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->get('primary_ip')) != 1) {
				return ['primary_ip' => 'IP address required'];
			}
			
			if ($this->get('ip') == $this->get('primary_ip')) {
				return ['primary_ip' => 'MUST not be equal to ip field'];
			}
			
			global $app;
			
			$ip_model = new PrimaryIp($app->db);
			
			if (!$this->isLoaded() && $ip_model->tryLoad($this->get('ip')) != null) {
				return ['ip' => 'IP address ' . $this->get('ip') . ' already exists in the table'];
			}	
			
			if (!empty($this->get('primary_ip'))) {
				if ($ip_model->tryLoad($this->get('primary_ip')) == null) {
					return ['primary_ip' => 'There is no primary ip address ' . $this->get('primary_ip') . ' in the ip addresses table - add it first'];
				}
				
				$script_model_1 = new Script($app->db);
				$script_model_1->addCondition('script_ip', $this->get('ip'));
				
				$script_model_2 = new Script($app->db);
				$script_model_2->addCondition('database_ip', $this->get('ip'));
				
				if ($script_model_1->tryLoadAny() != null || $script_model_2->tryLoadAny() != null) {
					return ['primary_ip' => 'IP address ' . $this->get('ip') . ' is used in scripts table, so it can not be secondary'];
				}
			}
		});
		
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_DELETE, function (\Atk4\Data\Model $m) {
			global $app;
			
			error_log(print_r("HOOK_BEFORE_DELETE Ip -" . $m->get('ip') . "-", true));
			
			$script_model_1 = new Script($app->db);
			$script_model_1->addCondition('script_ip', $m->get('ip'));
			
			$script_model_2 = new Script($app->db);
			$script_model_2->addCondition('database_ip', $m->get('ip'));
			
			if ($script_model_1->tryLoadAny() != null || $script_model_2->tryLoadAny() != null) {
				error_log(print_r("HOOK_BEFORE_DELETE Stop", true));
				$m->breakHook(false);
				return;
			}
		});
    }
}

?>
