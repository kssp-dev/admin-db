<?php

require_once 'MonitoringSeriesView.php';

class MonitoringLastAlert extends MonitoringSeriesView {
    public $table = 'monitoring.last_alerts';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Last Alert';

        $this->addFields([
			'time' => [
				'type' => 'datetime',
				'neverSave' => true,
				'ui' => [
					'filterModel' => DatetimeFilterModel::class
				]
			],
			'value' => [
				'type' => 'integer',
				'neverSave' => true,
				'ui' => [
					'filterModel' => MonitoringAlertValueFilterModel::class
				]
			],
			'uid' => [
				'neverSave' => true,
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'name' => [
				'neverSave' => true,
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'short_name' => [
				'neverSave' => true,
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			],
			'description' => [
				'type' => 'text',
				'neverSave' => true,
				'ui' => [
					'filterModel' => StringFilterModel::class
				]
			]
        ]);
		
		$this->idField = 'uid';
    }
}

?>
