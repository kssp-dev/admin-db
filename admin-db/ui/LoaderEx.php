<?php

class LoaderEx extends Atk4\Ui\Loader {
	
	function addMessage($caption, $output, $type) {
		$icon = null;
		
		switch ($type) {
			case 'info':
				$icon = 'info circle';
				break;
			case 'warning':
				$icon = 'exclamation triangle';
				break;
			case 'success':
				$icon = 'smile outline';
				break;
			case 'error':
				$icon = 'skull crossbones';
				break;
		}
		$msg = Atk4\Ui\Message::addTo($this, [
			$caption
			, 'icon' => $icon
			, 'type' => $type
		]);
		$msg->text->addParagraph('');
		
		foreach ($output as $str) {
			foreach (explode('<br>', $str) as $line) {
				$msg->text->addParagraph($line);
			}
		}
    }

	function addReloadButton($app, $uri) {
		Atk4\Ui\Button::addTo($this, [
			is_array($uri) ? $uri['caption'] : 'Reload site'
			,'icon' => 'sync'
		])	->addClass('big blue button')
			->on('click', $app->jsRedirect(
				is_array($uri) ? $uri['uri'] : strval($uri)
				, false
			));
	}

}

?>
