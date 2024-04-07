<?php

class ScriptEditor extends Atk4\Ui\Js\JsModal {

    function __construct(Atk4\Data\Model $entity, Atk4\Ui\View $vp) {
		$entity->assertIsEntity();
		
		global $app;
		
		$form = Atk4\Ui\Form::addTo($vp);
		$text = $form->addControl('text', ['caption' => $entity->get('name')], ['type' => 'text']);
		$text->rows = 30;
		$text->set($entity->get('script'));
		
		$form->onSubmit(function () use ($entity, $form) {
					$entity->set('script', $form->model->get('text'));
					$entity->save();
					usleep(500000);
		});
		
        parent::__construct(
			'Script Editor'
			, $vp
		);
    }
    
}

?>
