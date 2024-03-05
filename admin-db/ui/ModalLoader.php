<?php

class ModalLoader extends Atk4\Ui\Js\JsModal {

    function __construct($title, $app, $act) {
		$view = $app->add([Atk4\Ui\VirtualPage::class])
				->set(
					function (Atk4\Ui\VirtualPage $vp) use ($title, $act) {
						LoaderEx::addTo($vp, [
							'shim' => [
								\Atk4\Ui\Message::class
								, is_array($title)
									? $title['waiting']
									: strval($title) . '...'
								, 'class.red' => true
							]
						])->set($act);
					}
				);
		
        parent::__construct(
			is_array($title)
				? $title['title']
				: strval($title)
			, $view
		);
    }
    
}

?>
