<?php

class Script extends EmptyNullModel {
    public $table = 'scripts';

    protected function init(): void
    {
        parent::init();

        $this->addFields([
			  //"id",
			  "name" => ['required' => true],
			  "script_ip" => ['required' => true],
			  "script_file" => ['required' => true],
			  "script_path" => ['required' => true],
			  "timer_file",
			  "logic" => ['type' => 'text'],
			  "database_ip",
			  "database_name",
			  "database_table",
			  "description" => ['type' => 'text']
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('ScriptIp', ['model' => new PrimaryIp($this->getPersistence()), 'ourField' => 'script_ip', 'theirField' => 'ip']);
		$this->hasOne('DatabaseIp', ['model' => new PrimaryIp($this->getPersistence()), 'ourField' => 'database_ip', 'theirField' => 'ip']);
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->get('script_ip')) != 1) {
				return ['script_ip' => 'IP address required'];
			}
			
			if (!empty($this->get('database_ip')) && preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->get('database_ip')) != 1) {
				return ['database_ip' => 'IP address required'];
			}
			
			global $app;
			
			$ip_model = new PrimaryIp($app->db);
			
			if ($ip_model->tryLoad($this->get('script_ip')) == null) {
				return ['script_ip' => 'There is no primary ip address ' . $this->get('script_ip') . ' in the ip addresses table - add it first'];
			}
			
			if (!empty($this->get('database_ip')) && $ip_model->tryLoad($this->get('database_ip')) == null) {
				return ['database_ip' => 'There is no primary ip address ' . $this->get('database_ip') . ' in the ip addresses table - add it first'];
			}
			
			$script_model = new ScriptName($app->db);
			$script_model = $script_model->tryLoad($this->get('name'));
			
			if ($script_model != null && $script_model->get('id') != $this->get('id')) {
				return ['name' => 'Script of name "' . $this->get('name') . '" already exists in the table'];
			}
			
			$script_model = new ScriptFile($app->db);
			$script_model = $script_model->tryLoad($this->get('script_file'));
			
			if ($script_model != null && $script_model->get('id') != $this->get('id')) {
				return ['script_file' => 'Script file "' . $this->get('script_file') . '" already exists in the table'];
			}
		});
    }
}

?>
