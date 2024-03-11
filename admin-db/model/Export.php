<?php

require_once 'EmptyNullModel.php';

class Export extends EmptyNullModel {
    public $table = 'export';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Export Template';

        $this->addFields([
			"from" => ['required' => true],
			"to" => ['required' => true],
			"icon",
			"header" => ['type' => 'text'],
			"row" => ['type' => 'text'],
			"footer" => ['type' => 'text'],
			"detales" => ['type' => 'text'],
			"link" => ['type' => 'text']
        ]);
        
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_SAVE, function (\Atk4\Data\Model $m) {
			foreach ($m->getFields() as $name => $field) {
				if ($field->type == 'text' && !empty($this->get($name))) {
					$m->set($name, str_replace(
						["\n", "\r", "\t", "\v", "\e", "\f"]
						, ['{{\n}}', '{{\r}}', '{{\t}}', '{{\v}}', '{{\e}}', '{{\f}}']
						, $this->get($name)
					));
				}
			}
		});
		
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_DELETE, function (\Atk4\Data\Model $m) {
			foreach ($m->getFields() as $name => $field) {
				if ($field->type == 'text' && !empty($this->get($name))) {
					$m->breakHook(false);
					return;
				}
			}
		});
    }
}

?>
