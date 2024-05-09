<?php

class TimerView extends \Atk4\Ui\View {
	public $interval = 60000;	// ms
	public $query = 'touch';
	public $js = <<<'EOF'
		var s = Date.now();
		var i = setInterval(
			function () {
				var p = Date.now() - s;
				var el = $([]);
				//el.find('.detail').text(p + 'ms');
				
				var anHttpRequest = new XMLHttpRequest();
				
				anHttpRequest.onreadystatechange = function() {
					//if (anHttpRequest.readyState == 4 && anHttpRequest.status == 200) {
						el.find('.detail').text(anHttpRequest.responseText);
					//} else {
						//el.find('.detail').text(anHttpRequest.status);
					//}
				}

				anHttpRequest.open( "GET", "%URL%", true );
				anHttpRequest.send( null );
			}
		, %INTERVAL%);
	EOF;

	#[\Override]
	protected function init(): void
	{
		global $tab_uri;
		
		parent::init();

		$label = \Atk4\Ui\Label::addTo($this, ['Timer', 'detail' => 'text', 'class.hidden' => true]);
		
		$script = str_replace('%INTERVAL%', $this->interval, $this->js);
		if (empty($this->query))
			$script = str_replace('%URL%', $tab_uri, $script);
		else
			$script = str_replace('%URL%', $tab_uri . '?' . $this->query, $script);

		$this->js(true, new \Atk4\Ui\Js\JsExpression($script, [$label]));
	}

}

?>
