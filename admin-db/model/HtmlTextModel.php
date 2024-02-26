<?php

class HtmlTextModel extends EmptyNullModel {

    protected function init(): void
    {
        parent::init();
     
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_SAVE, function (\Atk4\Data\Model $m) {
			foreach ($m->getFields() as $name => $field) {
				if ($field->type == 'text' && !empty($this->get($name))) {
					$this->set($name, str_replace(array("\r\n", "\r", "\n"), "<br>", htmlspecialchars($this->get($name))) );
				}
			}
		});
		
		$this->onHook(\Atk4\Data\Model::HOOK_AFTER_LOAD, function (\Atk4\Data\Model $m) {
			foreach ($m->getFields() as $name => $field) {
				if ($field->type == 'text' && !empty($this->get($name))) {
					$this->set($name, htmlspecialchars_decode(str_replace("<br>", "\r\n", $this->get($name))) );
				}
			}
		});
    }
}

?>
