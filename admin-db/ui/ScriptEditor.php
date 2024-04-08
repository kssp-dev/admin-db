<?php

class ScriptEditor extends Atk4\Ui\Js\JsModal {

    function __construct(Atk4\Data\Model $entity, Atk4\Ui\View $vp) {
		$entity->assertIsEntity();
		
		global $app;
		
		$form = Atk4\Ui\Form::addTo($vp);
		$form->setModel($entity, ['script']);
		$form->getControl('script')->rows = 30;
		$form->getControl('script')->caption = $entity->get('name');
		
		$form->onSubmit(function (Atk4\Ui\Form $form) {
			$form->model->save();
			
			return new Atk4\Ui\Js\JsToast('Saved successfully!');
		});
		
        parent::__construct(
			'Script Editor'
			, $vp
		);
    }
    
}

?>
