<?php

require_once 'EmptyNullModel.php';

class MonitoringType extends EmptyNullModel {
    public $table = 'monitoring.types';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Type';

        $this->addFields([
			'is_alert' => [
				'type' => 'boolean',
				'nullable' => false,
				'ui' => [
					'filterModel' => BooleanFilterModel::class
				]
			],
			'name' => [
				'required' => true,
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'uid' => [
				'required' => true,
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'notification_delay' => [
				'type' => 'integer',
				'nullable' => false,
				'ui' => [
					'filterModel' => NumberFilterModel::class
				]
			],
			'notification_period' => [
				'type' => 'integer',
				'nullable' => false,
				'ui' => [
					'filterModel' => NumberFilterModel::class
				]
			],
			'description' => [
				'type' => 'text',
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			]
        ]);
        
        $this->getField('id')->neverSave = true;		
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			$m = clone $this->getModel();
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique name'];
			}
			
			if (preg_match('/^[^@#\s]+$/', $this->get('uid')) != 1) {
				return ['uid' => '@, # or blank forbidden'];
			}
			
			if ($this->get('notification_delay') < 0) {
				return ['notification_delay' => 'Must be greater or equal zero'];
			}
			
			if ($this->get('notification_period') < 0) {
				return ['notification_period' => 'Must be greater or equal zero'];
			}
			
			if (
				$this->get('notification_period') > 0
				&& $this->get('notification_period') <= $this->get('notification_delay')
			) {
				return ['notification_period' => 'Must be zero or greater than delay'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('uid', $this->get('uid'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['uid' => 'Must have unique uid'];
			}
			
			if (preg_match('/[\'"]/', $this->get('description')) == 1) {
				return ['description' => 'Quotation mark forbidden'];
			}
		});		

		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_DELETE, function (\Atk4\Data\Model $entity) {
			$entity->assertIsEntity();
			$entity->deleteSeries();
		});
    }
    
    public function deleteSeries() {
		$this->assertIsEntity();
		
		global $app;
		
		$delete = $app->db->initQuery(new MonitoringSeries($app->db));
		$delete->mode('delete');
		$delete->where('type_id', $this->get('id'));
		$count = $delete->executeStatement();
		
		return $count . ' series rows of type "' . $this->get('name') . '" were deleted';
	}
    
    public function countSeries() {
		$this->assertIsEntity();
		
		global $app;
		
		$model = new MonitoringSeries($app->db);
		$model->addCondition('type_id', $this->get('id'));
		$count = $model->executeCountQuery();
		
		return $count;
	}
}

?>
