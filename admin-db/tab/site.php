<?php

$title = 'Site Administration';

require_once __DIR__ . '/../app.php';


Atk4\Ui\Button::addTo($app, [
	'Update the site'
	, 'icon' => 'download'
])
->addClass('big red button')
->on(
	'click',
	new ModalLoader('Updating the site',
		function (LoaderEx $p) {
			global $app, $app_dir;
			
			$output = [];
			$code = 0;
			$res = exec('php "' . $app_dir . 'tool/update.php" "' . getenv('ADMIN_DB_UPDATE_URL') . '"', $output, $code);
			
			if ($code == 0) {
				array_push($output, 'Database upgrading...');
				
				try {
					$script = file_get_contents($app_dir . 'tool/create.sql');
					
					if ($script) {
						$app->db->getConnection()->getConnection()->exec($script);
					} else {
						throw new Exception('SQL script reading error');
					}
					
					array_push($output, 'Database is up to date now');
				} catch (Exception $e) {
					array_push($output, $e->getMessage());
					$code = -1;
				}
			}
			
			$p->addMessage($code == 0
					? 'You are lucky - the site is up to date now'
					: 'Error occured - the site is probably ruined'
				, $output
				, $code == 0 ? 'success' : 'error'
			);
			
			$p->addReloadButton();
		}
	)
);

?>
