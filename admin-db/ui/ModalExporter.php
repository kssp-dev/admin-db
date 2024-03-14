<?php

require_once 'ModalLoader.php';

class ModalExporter extends ModalLoader {

    function __construct(Atk4\Data\Model $from_model, Atk4\Data\Model $export_entity, Atk4\Ui\View $vp = null) {
        parent::__construct(
			'Export ' . $export_entity->get('from') . ' to ' . $export_entity->get('to')
			, function (LoaderEx $p) use ($from_model, $export_entity) {
				global $app;
				
				$templates = [];
				
				foreach ($export_entity->getFields() as $name => $field) {
					if ($field->type == 'text') {
						$templates[$name] = str_replace(
							['{{\n}}', '{{\r}}', '{{\t}}', '{{\v}}', '{{\e}}', '{{\f}}']
							, ["\n", "\r", "\t", "\v", "\e", "\f"]
							, $export_entity->get($name) ?? ''
						);
					}
				}
				
				$replace = [];
				
				foreach ($from_model->getFields() as $name => $field) {
					switch ($field->type) {
						case 'date':
							$replace[$name] = function (Atk4\Data\Model $entity, $name) {
								return $entity -> get($name) -> format('Y-m-d');
							};
							break;
						default:
							$replace[$name] = function (Atk4\Data\Model $entity, $name) {
								return strval($entity -> get($name));
							};
					}
				}
				
				$output = [];
				
				if ($from_model->isEntity()) {		// Details export
					
					if (!empty($templates['details'])) {
						$str = $templates['details'];
						
						foreach ($replace as $name => $func) {
							$str = str_replace('{{' . $name . '}}', $func($from_model, $name), $str);
						}
						array_push($output, $str);
					}
				
					if ($from_model->get('name')) {
						$p->addHeader($from_model->get('name'), 3);
					}
					
				} else {							// Table export
				
					if (!empty($templates['header'])) {
						array_push($output, $templates['header']);
					}
					
					if (!empty($templates['row'])) {
						foreach ($from_model as $id => $entity) {
							$str = $templates['row'];
							
							foreach ($replace as $name => $func) {
								$str = str_replace('{{' . $name . '}}', $func($entity, $name), $str);
							}
							array_push($output, $str);
						}
					}
					
					if (!empty($templates['footer'])) {
						array_push($output, $templates['footer']);
					}
					
				}
				
				$p->addTextarea($output);
				
				$p->addCloseButton($app);
			}
			, $vp
		);
    }
    
}

?>
