<?php

$title = 'Site Administration';

require_once __DIR__ . '/../app.php';


if (! $features['admin']
	|| ! $app->auth->user->isLoaded()
) {
	$app->redirect($app_uri . "..");
	exit;
}


function scandirtree($path = '', &$name = array() )
{
	$path = $path == '' ? dirname(__FILE__) : $path;
	if (substr($path, -1) != DIRECTORY_SEPARATOR) {
		$path = $path.DIRECTORY_SEPARATOR;
	}
	$lists = @scandir($path);

	if(!empty($lists))
	{
		foreach($lists as $f)
		{
			if(is_dir($path.$f) && $f != ".." && $f != ".")
			{
				scandirtree($path.$f, $name);
			}
			else
			{
				$name[] = $path.$f;
			}
		}
	}
	return $name;
}


$fileTime = 0;

foreach (scandirtree($app_dir) as $file) {
	if (is_file($file) && $fileTime < filemtime($file)) {
		$fileTime = filemtime($file);
	}
}

\Atk4\Ui\Header::addTo($app, ['Release time ' . date($app->uiPersistence->datetimeFormat, $fileTime)]);


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

if (file_exists($app_dir . '../composer.phar')) {
	\Atk4\Ui\Header::addTo($app, ['Application']);
	\Atk4\Ui\View::addTo($app, ['ui' => 'segment', 'class.raised' => true, 'element' => 'pre'])
		//->set(print_r($app->uiPersistence, true));
		->set(print_r(scandirtree($app_dir), true));
}
/*
\Atk4\Ui\Header::addTo($app, ['_SERVER']);
\Atk4\Ui\View::addTo($app, ['ui' => 'segment', 'class.raised' => true, 'element' => 'pre'])->set(print_r($_SERVER, true));

\Atk4\Ui\Header::addTo($app, ['Environment']);
\Atk4\Ui\View::addTo($app, ['ui' => 'segment', 'class.raised' => true, 'element' => 'pre'])->set(print_r(getenv(), true));
*/
?>
