<?php

require_once 'ModalLoader.php';

class ModalExporter extends ModalLoader {

    function __construct(\Atk4\Data\Model $from_model, \Atk4\Data\Model $export_entity) {
        parent::__construct(
			'Export ' . $export_entity->get('from') . ' to ' . $export_entity->get('to')
			, function (LoaderEx $p) use ($from_model, $export_entity) {
				global $app;
				
				$output = '';
				for ($i = 1; $i <= 100; $i++) {
					$output = $output . $i . "\r\n";
				}
				$p->addTextarea($output);
				
				$p->addCloseButton($app);
			}
		);
    }
    
}

?>
