<?php

class Export extends EmptyNullModel {
    public $table = 'export';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Export Template';

        $this->addFields([
			"from" => ['required' => true],
			"to" => ['required' => true],
			"header" => ['type' => 'text'],
			"row" => ['type' => 'text'],
			"footer" => ['type' => 'text'],
			"link" => ['type' => 'text']
        ]);
		
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
