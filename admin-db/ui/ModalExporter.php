<?php

require_once 'ModalLoader.php';

class ModalExporter extends ModalLoader {

    function __construct(Atk4\Data\Model $fromModel, Atk4\Data\Model $exportEntity, Atk4\Ui\View $virtualPage = null) {
        parent::__construct(
			'Export ' . $exportEntity->get('from') . ' to ' . $exportEntity->get('to')
			, function (LoaderEx $p) use ($fromModel, $exportEntity) {
				global $app;
				
				$templates = [];
				
				foreach ($exportEntity->getFields() as $name => $field) {
					if ($field->type == 'text') {
						$templates[$name] = str_replace(
							['{{\n}}', '{{\r}}', '{{\t}}', '{{\v}}', '{{\e}}', '{{\f}}']
							, ["\n", "\r", "\t", "\v", "\e", "\f"]
							, $exportEntity->get($name) ?? ''
						);
					}
				}
				
				$replace = [];
				
				foreach ($fromModel->getFields() as $name => $field) {
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
				
				if ($fromModel->isEntity()) {		// Details export
					
					if (!empty($templates['details'])) {
						$str = $templates['details'];
						
						foreach ($replace as $name => $func) {
							$str = str_replace('{{' . $name . '}}', $func($fromModel, $name), $str);
						}
						array_push($output, $str);
					}
				
					if ($fromModel->get('name')) {
						$p->addHeader($fromModel->get('name'), 3);
					}
					
				} else {							// Table export
				
					if (!empty($templates['header'])) {
						array_push($output, $templates['header']);
					}
					
					if (!empty($templates['row'])) {
						foreach ($fromModel as $id => $entity) {
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
			, $virtualPage
		);
    }
    
}

?>
