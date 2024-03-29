<?php

class LoaderEx extends Atk4\Ui\Loader {
	
	function addHeader($text, $size) {
		Atk4\Ui\Header::addTo($this, [$text, 'size' => $size]);
    }
	
	function addTextarea($output) {
		$segment = Atk4\Ui\View::addTo($this, ['ui' => 'form']);
		
		Atk4\Ui\Form\Control\Textarea::addTo($segment, [[
			'ui' => 'fluid'
			, 'readOnly' => true
			, 'rows' => 14
		]])->set(is_array($output) ? implode($output) : strval($output));
		
		Atk4\Ui\View::addTo($this, ['ui' => 'hidden divider']);
    }
	
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

	function addRedirectButton($uri) {
		global $app;
		
		Atk4\Ui\Button::addTo($this, [
			is_array($uri) ? $uri[0] : 'Redirect'
			,'icon' => is_array($uri) ? $uri['icon'] : 'sign in alternate'
		])	->addClass('blue button')
			->on('click', $app->jsRedirect(
				is_array($uri) ? $uri['uri'] : strval($uri)
				, false
			));
	}

	function addReloadButton($uri = null) {
		global $app_uri;
		
		$this->addRedirectButton([
			'Reload site'
			, 'icon' => 'sync'
			, 'uri' => isset($uri) ? $uri : $app_uri . '..'
		]);
	}

	function addCloseButton() {
		global  $tab_uri;
		
		$this->addRedirectButton([
			'Close'
			, 'icon' => 'times'
			, 'uri' => $tab_uri
		]);
	}

}

?>
