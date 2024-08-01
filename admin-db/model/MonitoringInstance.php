<?php

class MonitoringInstance extends \Atk4\Data\Model {
    public $table = 'monitoring.instances';

    protected function init(): void
    {
        parent::init();

		$this->caption = 'Monitoring Instance';

        $this->addFields([
			'enabled' => [
				'type' => 'boolean',
				'nullable' => false,
				'ui' => [
					'filterModel' => BooleanFilterModel::class
				]
			],
			'instance' => [
				'required' => true,
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'name' => [
				'required' => true,
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'script_timeout' => [
				'required' => true,
				'ui' => [
					'filterModel' => NumberFilterModel::class
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

		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if (preg_match('/^[^\s]+$/', $this->get('instance')) != 1) {
				return ['instance' => 'Blank forbidden'];
			}

			$m = clone $this->getModel();
			$m->addCondition('instance', $this->get('instance'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique instance'];
			}

			$m = clone $this->getModel();
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique name'];
			}

			if ($this->get('script_timeout') <= 0) {
				return ['script_timeout' => 'Must be a positive integer'];
			}
		});
    }

    public function countTargets() {
		$this->assertIsEntity();

		global $app;

		$model = new MonitoringTarget($app->db);
		$model->addCondition('instance_id', $this->get('id'));
		$count = $model->executeCountQuery();

		return $count;
	}
}

?>
