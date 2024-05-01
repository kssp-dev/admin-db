<?php

$title = 'Users';

require_once __DIR__ . '/../app.php';

if (! $app->auth->user->isLoaded()
	&& $app->auth->user->getModel()->executeCountQuery() != 0
) {
	$app->redirect($app_uri . "..");
	exit;
}

$model = new LoginUser($app->db);
$model->setOrder('name');

$model->getUserAction('delete')->confirmation = true;
$model->getUserAction('registerNewUser')->system = true;

$crud = \Atk4\MasterCrud\MasterCrud::addTo($app);

$crudOptions = [
	[
		'_crud' => [
			'displayFields' => ['name', 'email']
			, 'addFields' => ['name', 'email', 'password']
			, 'editFields' => ['name', 'email', 'password']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['name', 'email']
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
