<?php

class JsModal extends \Atk4\Ui\Js\JsModal {

	public static function addCloseButton(\Atk4\Ui\View $view) {
		global $tab_uri;
		$button = \Atk4\Ui\Button::addTo($view, [
			'Close'
			, 'icon' => 'times'
		])->addClass('ui blue basic button');
		$button->on('click', $view->getApp()->jsRedirect($tab_uri, false));
		return $button;
	}

	public static function addRedirectButton(\Atk4\Ui\View $view, $uri, $caption) {
		$button = \Atk4\Ui\Button::addTo($view, [
			$caption
			, 'icon' => 'arrow right'
		])->addClass('ui right labeled icon right floated blue basic button');
		$button->on('click', $view->getApp()->jsRedirect($uri, true));
		return $button;
	}

}

?>
