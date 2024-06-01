<?php

class ScriptEditor extends \Atk4\Ui\Js\JsModal {
	public $scriptField = 'script';

    function __construct(\Atk4\Data\Model $entity, \Atk4\Ui\View $vp, \Atk4\Ui\View $table) {
		global $tab_uri;
		
		$entity->assertIsEntity();
		$scriptField = $this->scriptField;
		$bakupScript = $entity->get($scriptField);
		
        $vp->js(true, new \Atk4\Ui\Js\JsExpression('
			var view = document.getElementById([]);
			view.className += " overlay fullscreen";
			view.style.height = document.documentElement.clientHeight + "px";
			', [$vp->stickyArgs['__atk_m']]
		));
		
		$form = \Atk4\Ui\Form::addTo($vp);
		$form->setModel($entity, [$scriptField]);
		$control = $form->getControl($scriptField);
		$control->height = 79;
		$control->fontSize = 15;
		$control->caption = $entity->get('name');
		
		$form->onSubmit(function (\Atk4\Ui\Form $form) use ($table, $scriptField, $bakupScript) {
			if (0 == strcmp($bakupScript, $form->model->get($scriptField))) {
				return new \Atk4\Ui\Js\JsToast([
					'class' => 'warning',
					'message' => 'No change to save!'
				]);
			} else {
				$form->model->set('login', $form->getApp()->auth->user->get('login'));
				$form->model->set('updated', new DateTime());
				$form->model->save();
				
				$bakupScript = $form->model->get($scriptField);
				
				return new \Atk4\Ui\Js\JsBlock([
					$table->jsReload(),
					new \Atk4\Ui\Js\JsToast('Saved successfully!')
				]);
			}
		});
		
		\Atk4\Ui\Button::addTo($form, [
			'Close'
		])->on('click', $form->getApp()->jsRedirect($tab_uri, false));
		
        parent::__construct(
			'Script Editor'
			, $vp
		);
    }
    
}

?>
