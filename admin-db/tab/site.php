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
