<?php

class AdminTab {

    function __construct() {
		
        global $app, $app_uri, $app_dir;
        
		Atk4\Ui\Button::addTo($app, [
			'Download database initialization script'
			, 'icon' => 'database'
		])
		->addClass('big blue button')
		->on('click', $app->jsRedirect($app_uri . 'tool/create.sql', false));

		if (file_exists($app_dir . '../composer.phar')) {
			Atk4\Ui\Button::addTo($app, [
				'Recreate database structure'
				, 'icon' => 'snowplow'
			])
			->addClass('big red button')
			->on(
				'click',
				new ModalLoader('Creating database structure', $app,
					function (LoaderEx $p) {
						global $app, $app_uri, $app_dir;
						$code = 0;
						$output = [];
						
						try {
							$script = file_get_contents($app_dir . 'tool/create.sql');
							if ($script) {
								$app->db->getConnection()->getConnection()->exec($script);
								array_push($output, 'Database is empty now');
							} else {
								throw new Exception('SQL script reading error');
							}
						} catch (Exception $e) {
							array_push($output, $e->getMessage());
							$code = -1;
						}
						
						$p->addMessage($code == 0
								? 'Database recreated succesfully'
								: 'Error occured - database is probably ruined'
							, $output
							, $code == 0 ? 'success' : 'error'
						);
						
						$p->addReloadButton($app, $app_uri . '..');
					}
				)
			);
		}

		Atk4\Ui\View::addTo($app, ['ui' => 'hidden divider']);

		Atk4\Ui\Button::addTo($app, [
			'Update the site'
			, 'icon' => 'download'
		])
		->addClass('big red button')
		->on(
			'click',
			new ModalLoader('Updating the site', $app,
				function (LoaderEx $p) {
					global $app, $app_uri, $app_dir;
					
					$output = [];
					$code = 0;
					$res = exec('php "' . $app_dir . 'tool/update.php" "' . getenv('ADMIN_DB_UPDATE_URL') . '"', $output, $code);
					
					$p->addMessage($code == 0
							? 'You are lucky - the site is up to date now'
							: 'Error occured - the site is probably ruined'
						, $output
						, $code == 0 ? 'success' : 'error'
					);
					
					$p->addReloadButton($app, $app_uri . '..');
				}
			)
		);

    }

}

?>
