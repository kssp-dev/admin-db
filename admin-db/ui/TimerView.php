<?php

class TimerView extends \Atk4\Ui\View {
	public $interval = null;	// ms
	public $query = 'touch';
	public $hidden = true;
	public $js = '
		var s = Date.now();
		var i = setInterval(
			function () {
				var p = Date.now() - s;
				var el = $([]);
				//el.find(".detail").text(p + "ms");

				var anHttpRequest = new XMLHttpRequest();

				anHttpRequest.onreadystatechange = function() {
					//if (anHttpRequest.readyState == 4 && anHttpRequest.status == 200) {
						el.find(".detail").text(anHttpRequest.responseText);
					//} else {
						//el.find(".detail").text(anHttpRequest.status);
					//}
				}

				anHttpRequest.open( "GET", "%URL%", true );
				anHttpRequest.send( null );
			}
		, %INTERVAL%);
	';

	#[\Override]
	protected function init(): void
	{
		global $tab_uri;

		if ($this->interval == null) {
			$this->interval = intval(ini_get('session.gc_maxlifetime')) * 250;	// 4 times per session timeout
		}

		parent::init();

		$label = \Atk4\Ui\Label::addTo($this, ['Timer'
			, 'detail' => print_r($this->interval, true)
			, 'class.hidden' => $this->hidden
		]);

		$script = str_replace('%INTERVAL%', $this->interval, $this->js);
		if (empty($this->query))
			$script = str_replace('%URL%', $tab_uri, $script);
		else
			$script = str_replace('%URL%', $tab_uri . '?' . $this->query, $script);

		$this->js(true, new \Atk4\Ui\Js\JsExpression($script, [$label]));
	}

}

?>
