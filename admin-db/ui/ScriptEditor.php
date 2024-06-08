<?php

class ScriptEditor extends \Atk4\Ui\Js\JsModal {
	public $scriptField = 'script';

    function __construct(\Atk4\Data\Model $entity, \Atk4\Ui\View $virtualPage, \Atk4\Ui\View $table) {
		global $tab_uri;

		$entity->assertIsEntity();
		$scriptField = $this->scriptField;
		$bakupScript = $entity->get($scriptField);

        $virtualPage->js(true, new \Atk4\Ui\Js\JsExpression('
			var view = document.getElementById([]);
			view.className += " overlay fullscreen";
			view.style.height = document.documentElement.clientHeight + "px";
			', [$virtualPage->stickyArgs['__atk_m']]
		));

		$form = \Atk4\Ui\Form::addTo($virtualPage);
		$form->setModel($entity, [$scriptField]);
		$control = $form->getControl($scriptField);
		$control->height = 79;
		$control->fontSize = 13;
		$control->caption = $entity->get('name') . ' [' . $entity->get('uid') . ']';

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
			, 'icon' => 'times'
		])->addClass('ui blue basic button')
		->on('click', $form->getApp()->jsRedirect($tab_uri, false));

		\Atk4\Ui\Button::addTo($form, [
			'Targets'
			, 'icon' => 'arrow right'
		])->addClass('ui right labeled icon right floated blue basic button')
		->on('click', $form->getApp()->jsRedirect('monitoring-targets', true));

        parent::__construct(
			'Script Editor'
			, $virtualPage
		);
    }

}

?>
