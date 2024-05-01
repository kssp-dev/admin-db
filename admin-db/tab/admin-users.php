<?php

$title = 'Users';

require_once __DIR__ . '/../app.php';

if (! $app->auth->user->isLoaded()
	&& $app->auth->user->getModel()->executeCountQuery() != 0
	&& empty($query_string)
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
			'displayFields' => ['name', 'login', 'email']
			, 'addFields' => ['name', 'login', 'password', 'email']
			, 'editFields' => ['name', 'login', 'password', 'email']
			, 'ipp' => 14
		]
	]
	, 'quickSearch' => ['name', 'login', 'email']
	, 'menuActions' => []
	, 'columnActions' => []
];

$crud->setModel($model, $crudOptions);

?>
