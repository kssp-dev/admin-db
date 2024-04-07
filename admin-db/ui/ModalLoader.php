<?php

require_once 'LoaderEx.php';

class ModalLoader extends Atk4\Ui\Js\JsModal {

    function __construct($title, \Closure $act, Atk4\Ui\View $vp = null) {
		global $app;
		
		$view = $vp;
		
		$func = function (Atk4\Ui\View $p) use ($title, $act) {
			LoaderEx::addTo($p, [
				'shim' => [
					\Atk4\Ui\Message::class
					, is_array($title)
						? $title['waiting']
						: strval($title) . '...'
					, 'class.red' => true
				]
			])->set($act);
		};
		
		if (isset($view)) {			
			$func($view);
		} else {
			$view = $app->add([Atk4\Ui\VirtualPage::class])
				->set($func);
		}
		
        parent::__construct(
			is_array($title)
				? $title['title']
				: strval($title)
			, $view
		);
    }
    
}

?>
