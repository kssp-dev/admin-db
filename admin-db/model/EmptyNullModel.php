<?php

class EmptyNullModel extends \Atk4\Data\Model {

    protected function init(): void
    {
        parent::init();
        
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_SAVE, function (\Atk4\Data\Model $m) {
			error_log(print_r("HOOK_BEFORE_SAVE idField -" . $m->idField . "-", true));
			foreach ($m->getFields() as $name => $field) {
				if ($m->idField != $name && empty($m->get($name))) {
					error_log(print_r("HOOK_BEFORE_SAVE set -" . $name . "- null", true));
					$m->set($name, null);
				}
			}
		});
    }
}

?>
