<?php

require_once 'MonitoringSeriesView.php';

class MonitoringLastMetric extends MonitoringSeriesView {
    public $table = 'monitoring.last_metrics';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Last Metric';

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
					'filterModel' => NumberFilterModel::class
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
