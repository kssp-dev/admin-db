<?php

class AdminTab {

    function __construct() {
        //parent::__construct();
        
        global $app, $app_uri;
        
		Atk4\Ui\Button::addTo($app, [
			'Download database initialization script'
			, 'icon' => 'database'
		])
		->addClass('big blue button')
		->on('click', $app->jsRedirect($app_uri . 'tool/create.sql', false));

		Atk4\Ui\Button::addTo($app, [
			'Recreate database structure'
			, 'icon' => 'snowplow'
		])
		->addClass('big red button')
		->on(
			'click',
			new Atk4\Ui\Js\JsModal('Creating database structure'
				, $app->add([Atk4\Ui\VirtualPage::class])
				->set(
					function (Atk4\Ui\VirtualPage $vp) {
						Atk4\Ui\Loader::addTo($vp, ['shim' => [\Atk4\Ui\Message::class, 'Erasing all the data...', 'class.red' => true]])
						->set(
							function (\Atk4\Ui\Loader $p) {
								global $app, $app_dir;
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
								
								$this->addLoaderMessage($p
									, $code == 0
										? 'Database recreated succesfully'
										: 'Error occured - database is probably ruined'
									, $output
									, $code
								);
								$this->addLoaderRefreshButton($p);
							}
						);
					}
				)
			)
		);

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
							function (\Atk4\Ui\Loader $p) {
								global $app, $app_uri, $app_dir;
								
								$output = [];
								$code = 0;
								$res = exec('php "' . $app_dir . 'tool/update.php" "' . getenv('ADMIN_DB_UPDATE_URL') . '"', $output, $code);
								
								$this->addLoaderMessage($p
									, $code == 0
										? 'You are lucky - the site is up to date now'
										: 'Error occured - the site is probably ruined'
									, $output
									, $code
								);
								$this->addLoaderRefreshButton($p);
							}
						);
					}
				)
			)
		);

    }

	function addLoaderMessage(\Atk4\Ui\Loader $p, $caption, $output, $code) {
		$msg = Atk4\Ui\Message::addTo($p, [
			$caption
			, 'icon' => $code == 0 ? 'smile outline' : 'skull crossbones'
			, 'type' => $code == 0 ? 'success' : 'error'
		]);
		$msg->text->addParagraph('');
		
		foreach ($output as $str) {
			foreach (explode('<br>', $str) as $line) {
				$msg->text->addParagraph($line);
			}
		}
    }

	function addLoaderRefreshButton(\Atk4\Ui\Loader $p) {
		global $app, $app_uri;
							
		Atk4\Ui\Button::addTo($p, [
			'Reload site'
			,'icon' => 'sync'
		])	->addClass('big blue button')
			->on('click', $app->jsRedirect($app_uri . '..', false));
	}

}

?>
