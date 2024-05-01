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
			  'updated' => ['type' => 'date'],
			  'login',
        ]);
        
        $this->getField('id')->neverSave = true;
		
		$this->hasOne('instance_id', [
			'required' => true,
			'model' => new MonitoringInstance($this->getPersistence())
		])->addFields([
			'instance'
		]);
        
		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_SAVE, function (\Atk4\Data\Model $m) {
			global $app;
			$m->set('updated', new DateTime());
			$m->set('login', $app->auth->user->get('login'));
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

		$this->onHook(\Atk4\Data\Model::HOOK_BEFORE_DELETE, function (\Atk4\Data\Model $entity) {
			$entity->assertIsEntity();
			$entity->deleteTargets();
		});
    }
    
    public function deleteTargets() {
		$this->assertIsEntity();
		
		global $app;
		
		$target = new MonitoringTarget($app->db);
		$target->addCondition('script_id', $this->get('id'));
		
		$count = 0;
		
		foreach ($target as $id => $ent) {
			$ent->deleteLogs();
			$ent->deleteSeries();
		}
		
		$delete = $app->db->initQuery($target);
		$delete->mode('delete');
		$delete->where('script_id', $this->get('id'));
		$count += $delete->executeStatement();
		
		return $count . ' targets of script "' . $this->get('name') . '" were deleted';
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
    
    public function deleteLogs() {
		$this->assertIsEntity();
		
		global $app;
		
		$target = new MonitoringTarget($app->db);
		$target->addCondition('script_id', $this->get('id'));
		
		$log = new MonitoringLog($app->db);
		
		$count = 0;
		
		foreach ($target as $id => $ent) {
			$delete = $app->db->initQuery($log);
			$delete->mode('delete');
			$delete->where('target_id', $ent->get('id'));
			$count += $delete->executeStatement();
		}					
		
		return $count . ' log rows of script "' . $this->get('name') . '" were deleted';
	}
    
    public function countTargets() {
		$this->assertIsEntity();
		
		global $app;
		
		$model = new MonitoringTarget($app->db);
		$model->addCondition('script_id', $this->get('id'));
		$count = $model->executeCountQuery();
		
		return $count;
	}
    
    public function countSeries() {
		$this->assertIsEntity();
		
		global $app;
		
		$target = new MonitoringTarget($app->db);
		$target->addCondition('script_id', $this->get('id'));
		
		$count = 0;
		
		foreach ($target as $id => $ent) {
			$count += $ent->countSeries();
		}	
		
		return $count;
	}
    
    public function countLogs() {
		$this->assertIsEntity();
		
		global $app;
		
		$target = new MonitoringTarget($app->db);
		$target->addCondition('script_id', $this->get('id'));
		
		$count = 0;
		
		foreach ($target as $id => $ent) {
			$count += $ent->countLogs();
		}	
		
		return $count;
	}
	
}

?>
