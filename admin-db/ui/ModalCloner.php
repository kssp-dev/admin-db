<?php

class ModalCloner extends Atk4\Ui\Js\JsModal {

    function __construct(Atk4\Data\Model $entity, Atk4\Ui\View $vp, Atk4\Ui\View $table, ?array $fields = null) {
		$entity->assertIsEntity();
		
		$form = Atk4\Ui\Form::addTo($vp);
		$form->setModel($entity, $fields);
		
		$form->onSubmit(function (Atk4\Ui\Form $form) use ($table) {
			$row =[];
					
			foreach ($form->model->getFields() as $key=>$field) {
				if ($key != $form->model->idField) {
					$row[$key] = $form->model->get($key);
				}
			}
			if ($row['enabled']) {
				$row['enabled'] = false;
			}
					
			$form->model->getModel()->insert($row);
			
			return new \Atk4\Ui\Js\JsBlock([
				$table->jsReload(),
				new Atk4\Ui\Js\JsToast('Saved successfully!')
			]);
		});
		
        parent::__construct(
			$entity->caption . ' Cloner'
			, $vp
		);
    }
    
}

?>
