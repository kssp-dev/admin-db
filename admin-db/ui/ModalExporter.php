<?php

require_once 'ModalLoader.php';

class ModalExporter extends ModalLoader {

    function __construct(Atk4\Data\Model $from_model, Atk4\Data\Model $export_entity) {
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
							, $export_entity->get($name)
						);
					}
				}
				
				$output = [];
				
				if (!empty($templates['header'])) {
					array_push($output, $templates['header']);
				}
				
				if (!empty($templates['row'])) {
					$replace = [];
					
					foreach ($from_model->getFields() as $name => $field) {
						error_log(print_r("Export field " . $name . " of type " . print_r($field->type, true), true));
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
				
				$p->addTextarea($output);
				
				$p->addCloseButton($app);
			}
		);
    }
    
}

?>
