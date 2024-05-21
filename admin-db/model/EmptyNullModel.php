<?php

class EmptyNullModel extends Atk4\Data\Model {

    protected function init(): void
    {
        parent::init();
        
		$this->onHook(Atk4\Data\Model::HOOK_BEFORE_SAVE, function (Atk4\Data\Model $m) {
			foreach ($m->getFields() as $name => $field) {
				if ($field->nullable
					&& ! $field->readOnly
					&& $m->idField != $name
					&& empty($m->get($name))
				) {
					$m->set($name, null);
				}
			}
		});
    }
}

?>
