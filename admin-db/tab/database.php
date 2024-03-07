<?php

$title = 'Database Administration';

require_once __DIR__ . '/../app.php';


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
		new ModalLoader('Creating database structure',
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

?>
