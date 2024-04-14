<?php

class ScriptEditor extends Atk4\Ui\Js\JsModal {

    function __construct(Atk4\Data\Model $entity, Atk4\Ui\View $vp, \Atk4\Ui\View $table) {
		$entity->assertIsEntity();
		
		global $app;
		
		$form = Atk4\Ui\Form::addTo($vp);
		$form->setModel($entity, ['script']);
		$control = $form->getControl('script');
		$control->rows = 30;
		$control->caption = $entity->get('name');
		$control->setInputAttr('style', 'font-family: monospace; font-weight: bold;');
		
		$form->onSubmit(function (Atk4\Ui\Form $form) use ($table) {
			$form->model->save();
			
			return new \Atk4\Ui\Js\JsBlock([
				$table->jsReload(),
				new \Atk4\Ui\Js\JsToast('Saved successfully!')
			]);
		});
		
        parent::__construct(
			'Script Editor'
			, $vp
		);
    }
    
}

?>
