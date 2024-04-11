<?php

class MonitoringScript extends \Atk4\Data\Model {
    public $table = 'monitoring.scripts';

    protected function init(): void
    {
        parent::init();
		
		$this->caption = 'Monitoring Script';

        $this->addFields([
			  'enabled' => ['type' => 'boolean', 'nullable' => false],
			  'name' => ['required' => true],
			  'uid' => ['required' => true],
			  'script' => ['type' => 'text'],
			  'updated' => ['type' => 'date']
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('instance_id', [
			'required' => true,
			'model' => new MonitoringInstance($this->getPersistence())
		])->addFields([
			'instance'
		]);
        
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_SAVE, function (\Atk4\Data\Model $m) {
			$m->set('updated', new DateTime());
		});
		
		$this->onHookShort(\Atk4\Data\Model::HOOK_VALIDATE, function () {
			if (preg_match('/^[^@#\s]+$/', $this->get('uid')) != 1) {
				return ['uid' => '@, # or blank forbidden'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('name', $this->get('name'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['name' => 'Must have unique name'];
			}
			
			$m = clone $this->getModel();
			$m->addCondition('uid', $this->get('uid'));
			$m = $m->tryLoadAny();
			if ($m != null && $m->get('id') != $this->get('id')) {
				return ['uid' => 'Must have unique text id'];
			}
			
			if (empty($this->get('script'))) {
				$this->set('script', '');
			}
		});
    }
    
    public function deleteSeries() {
		$this->assertIsEntity();
		
		global $app;
		
		$target = new MonitoringTarget($app->db);
		$target->addCondition('script_id', $this->get('id'));
		
		$series = new MonitoringSeries($app->db);
		
		$count = 0;
		
		foreach ($target as $id => $ent) {
			$delete = $app->db->initQuery($series);
			$delete->mode('delete');
			$delete->where('target_id', $ent->get('id'));
			$count += $delete->executeStatement();
		}					
		
		return $count . ' series rows of script "' . $this->get('name') . '" were deleted';
	}
}

?>
