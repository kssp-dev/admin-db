<?php

require_once 'EmptyNullModel.php';

class MonitoringTarget extends EmptyNullModel {
    public $table = 'monitoring.targets';

    protected function init(): void
    {
        parent::init();

		$this->caption = 'Monitoring Target';

        $this->addFields([
			'enabled' => [
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
			'period' => [
				'type' => 'integer',
				'required' => true,
				'ui' => [
					'filterModel' => NumberFilterModel::class
				]
			],
			'target' => [
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'script_data' => [
				'type' => 'text',
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'duration' => [
				'type' => 'integer',
				'readOnly' => true,
				'ui' => [
					'filterModel' => NumberFilterModel::class
				]
			],
        ]);

        $field = $this->getField('id');
        $field->neverSave = true;
        $field->ui = ['filterModel' => NumberFilterModel::class];

		$this->hasOne('script_id', [
			'required' => true,
			'model' => new MonitoringScript($this->getPersistence())
		])->addFields([
			'script_uid' => 'uid',
			'script_name' => 'name'
		]);

		$this->hasOne('instance_id', [
			'required' => true,
			'model' => new MonitoringInstance($this->getPersistence())
		])->addFields([
			'instance'
		]);

		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique name'];
			}

			if (preg_match('/^[^@#\s]+$/', $this->get('uid')) != 1) {
				return ['uid' => '@, # or blank forbidden'];
			}

			$m = clone $this->getModel();
			$m->addCondition('script_id', $this->get('script_id'));
			$m->addCondition('uid', $this->get('uid'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['uid' => 'Must have unique text id'];
			}

			if ($this->get('period') <= 0) {
				return ['period' => 'Must be a positive integer'];
			}
		});

		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_DELETE, function (\Atk4\Data\Model $entity) {
			$entity->assertIsEntity();
			$entity->deleteLogs();
			$entity->deleteSeries();
		});
    }

    public function deleteSeries() {
		$this->assertIsEntity();

		global $app;

		$delete = $app->db->initQuery(new MonitoringSeries($app->db));
		$delete->mode('delete');
		$delete->where('target_id', $this->get('id'));
		$count = $delete->executeStatement();

		return $count . ' series rows of target "' . $this->get('name') . '" were deleted';
	}

    public function countSeries() {
		$this->assertIsEntity();

		global $app;

		$model = new MonitoringSeries($app->db);
		$model->addCondition('target_id', $this->get('id'));
		$count = $model->executeCountQuery();

		return $count;
	}

    public function deleteLogs() {
		$this->assertIsEntity();

		global $app;

		$delete = $app->db->initQuery(new MonitoringLog($app->db));
		$delete->mode('delete');
		$delete->where('target_id', $this->get('id'));
		$count = $delete->executeStatement();

		return $count . ' log rows of target "' . $this->get('name') . '" were deleted';
	}

    public function countLogs() {
		$this->assertIsEntity();

		global $app;

		$model = new MonitoringLog($app->db);
		$model->addCondition('target_id', $this->get('id'));
		$count = $model->executeCountQuery();

		return $count;
	}

}

?>
