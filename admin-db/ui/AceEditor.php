<?php

class AceEditor extends \Atk4\Ui\Form\Control\Input {
	public $height = 20;		// Percent of client area
	public $fontSize = 10;		// pt
        
        
    #[\Override]
    public function getInput()
    {
        return $this->getApp()->getTag('textarea', array_merge([
            'name' => $this->shortName,
            'rows' => $this->height,
            'placeholder' => $this->placeholder,
            'style' => 'font-family: monospace; font-weight: bold; font-size: ' . $this->fontSize . 'pt;',
            'id' => $this->name . '_input',
            'disabled' => $this->disabled,
            'readonly' => $this->readOnly && !$this->disabled,
        ], $this->inputAttr), $this->getValue() ?? '');
    }
    

    public function renderView(): void
    {
		$js = '
			var textarea = $([]);

			var editDiv = $("<div>", {
				id: textarea.attr("id") + "_editor",
				position: "absolute",
				width: textarea.width(),
				height: textarea.attr("rows") * document.documentElement.clientHeight / 100,
				//height: textarea.height(),
				class: textarea.attr("class"),
				//style: textarea.attr("style")
			}).insertBefore(textarea);

			textarea.css("display", "none");

			var editor = ace.edit(editDiv.attr("id"));
			//editor.renderer.setShowGutter(false);
			//editor.setTheme("ace/theme/idle_fingers");
			editor.setFontSize(textarea.css("font-size"));
			$("label[for=" + textarea.attr("id") + "]").css("font-size", textarea.css("font-size"));
			
			editor.getSession().on("change", function () {
				var val = editor.getSession().getValue();
				
				var currentMode = editor.getSession().$modeId;
				var textMode = "ace/mode/text";
				
				if (/^#!\/.+\/bash\s*/.test(val)) {
					textMode = "ace/mode/sh";
				} else if (/^#!\/.+\s+python.*/.test(val)) {
					textMode = "ace/mode/python";
				} else if (/^\<\?php\s*/.test(val)) {
					textMode = "ace/mode/php";
				} else if (/^#!\/.+\/perl\s*/.test(val)) {
					textMode = "ace/mode/perl";
				}
				
				if (currentMode != textMode) {
					editor.getSession().setMode(textMode);
				}
				
				textarea.val(val);
			});
			
			editor.getSession().setValue(textarea.val());
		';
	
        $this->js(true, new \Atk4\Ui\Js\JsExpression($js, ['#' . $this->name . '_input']));
        
        parent::renderView();
    }

}
