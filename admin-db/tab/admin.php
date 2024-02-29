<?php

$title = 'Administration';

require_once __DIR__ . '/../app.php';


Atk4\Ui\Button::addTo($app, [
	'Download database initialization script'
	, 'icon' => 'database'
])
->addClass('big blue button')
->on('click', $app->jsRedirect($app_uri . 'tool/create.sql', false));

Atk4\Ui\View::addTo($app, ['ui' => 'hidden divider']);

Atk4\Ui\Button::addTo($app, [
	'Update the site'
	, 'icon' => 'download'
])
->addClass('big red button')
->on(
	'click',
	new Atk4\Ui\Js\JsModal('Updating the site'
		, $app->add([Atk4\Ui\VirtualPage::class])
		->set(
			function (Atk4\Ui\VirtualPage $vp) {
				Atk4\Ui\Loader::addTo($vp, ['shim' => [\Atk4\Ui\Message::class, 'Update in progress...', 'class.red' => true]])
				->set(
					function (\Atk4\Ui\Loader $p) use ($vp) {
						global $app, $app_dir, $app_uri;
						
						$output = [];
						$code = 0;
						$res = exec('php "' . $app_dir . 'tool/update.php" "' . getenv('ADMIN_DB_UPDATE_URL') . '"', $output, $code);
						
						$msg = Atk4\Ui\Message::addTo($p, [
							$code == 0 ? 'You are lucky - the site is up to date now' : 'Error occured - the site is probably ruined'
							, 'icon' => $code == 0 ? 'smile outline' : 'skull crossbones'
							, 'type' => $code == 0 ? 'success' : 'error'
						]);
						$msg->text->addParagraph('');
						
						foreach ($output as $str) {
							foreach (explode('<br>', $str) as $line) {
								$msg->text->addParagraph($line);
							}
						}
						
						Atk4\Ui\Button::addTo($p, [
							'Reload site'
							,'icon' => 'home'
						])	->addClass('big blue button')
							->on('click', $app->jsRedirect($app_uri . '..', false));
					}
				);
			}
		)
	)
);

?>
