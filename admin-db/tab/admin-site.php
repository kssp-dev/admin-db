<?php

$title = 'Site Administration';

require_once __DIR__ . '/../app.php';


if (! $features['admin']
	|| ! $app->auth->user->isLoaded()
) {
	$app->redirect($app_uri . "..");
	exit;
}

require_once __DIR__ . '/../tool/Filesystem.php';


$fileTime = 0;

foreach (Filesystem::scandirtree($app_dir, 0) as $file) {
	if (is_file($file) && $fileTime < filemtime($file)) {
		$fileTime = filemtime($file);
	}
}

\Atk4\Ui\Header::addTo($app, ['Current release time ' . date($app->uiPersistence->datetimeFormat, $fileTime)]);


\Atk4\Ui\Button::addTo($app, [
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
					
					$script = file_get_contents($app_dir . 'tool/templates.sql');

					if ($script && (new MonitoringScript($app->db))->executeCountQuery() == 0) {
						$app->db->getConnection()->getConnection()->exec($script);
					}

					array_push($output, 'Database is up to date now');
				} catch (Exception $e) {
					array_push($output, $e->getMessage());
					$code = -1;
				}
			}

			$p->addMessage($code == 0
					? 'You are lucky - the site is up to date now'
					: 'Error ' . $code . ' occured - the site is probably ruined'
				, $output
				, $code == 0 ? 'success' : 'error'
			);

			JsModal::addCloseButton($p);
		}
	)
);

if (file_exists($app_dir . '../composer.phar')) {
	\Atk4\Ui\Header::addTo($app, ['Application']);
	\Atk4\Ui\View::addTo($app, ['ui' => 'segment', 'class.raised' => true, 'element' => 'pre'])
		//->set(print_r($app->uiPersistence, true));
		->set(print_r(Filesystem::scandirtree($app_dir, 0), true));
}
/*
\Atk4\Ui\Header::addTo($app, ['_SERVER']);
\Atk4\Ui\View::addTo($app, ['ui' => 'segment', 'class.raised' => true, 'element' => 'pre'])->set(print_r($_SERVER, true));

\Atk4\Ui\Header::addTo($app, ['Environment']);
\Atk4\Ui\View::addTo($app, ['ui' => 'segment', 'class.raised' => true, 'element' => 'pre'])->set(print_r(getenv(), true));
*/
?>
