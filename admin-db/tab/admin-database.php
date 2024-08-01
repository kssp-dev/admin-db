<?php

$title = 'Database Administration';

require_once __DIR__ . '/../app.php';


if (! $features['admin']
	|| ! $app->auth->user->isLoaded()
) {
	$app->redirect($app_uri . "..");
	exit;
}


\Atk4\Ui\Button::addTo($app, [
	'Download database remove structure script'
	, 'icon' => 'eraser'
])
->addClass('big blue button')
->on('click', $app->jsRedirect($app_uri . 'tool/drop.sql', false));

if (file_exists($app_dir . '../composer.phar')) {
	\Atk4\Ui\Button::addTo($app, [
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
					$drop = file_get_contents($app_dir . 'tool/drop.sql');
					$create = file_get_contents($app_dir . 'tool/create.sql');

					if ($drop && $create) {
						$app->db->getConnection()->getConnection()->exec($drop);
						$app->db->getConnection()->getConnection()->exec($create);
					} else {
						throw new Exception('SQL script reading error');
					}

					array_push($output, 'Database is empty now');
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

				JsModal::addCloseButton($p);
			}
		)
	);
}

\Atk4\Ui\View::addTo($app, ['ui' => 'hidden divider']);

\Atk4\Ui\Button::addTo($app, [
	'Download database update script'
	, 'icon' => 'database'
])
->addClass('big blue button')
->on('click', $app->jsRedirect($app_uri . 'tool/create.sql', false));

\Atk4\Ui\Button::addTo($app, [
	'Update database structure'
	, 'icon' => 'forward'
])
->addClass('big orange button')
->on(
	'click',
	new ModalLoader('Updating database structure',
		function (LoaderEx $p) {
			global $app, $app_dir;
			$code = 0;
			$output = [];

			try {
				$script = file_get_contents($app_dir . 'tool/create.sql');

				if ($script) {
					$app->db->getConnection()->getConnection()->exec($script);
				} else {
					throw new Exception('SQL script reading error');
				}
					
				$script = file_get_contents($app_dir . 'tool/templates.sql');

				if ($script && (new MonitoringScript($app->db))->executeCountQuery() == 0) {
					$app->db->getConnection()->getConnection()->exec($script);
				}

				array_push($output, 'Database is up to date now');
			} catch (Exception $e) {
				array_push($output, $e->getMessage());
				$code = -1;
			}

			$p->addMessage($code == 0
					? 'Database updated succesfully'
					: 'Error occured - database is probably ruined'
				, $output
				, $code == 0 ? 'success' : 'error'
			);

			JsModal::addCloseButton($p);
		}
	)
);


?>
