<?php

class AdminDbApp extends \Atk4\Ui\App {
    public $title = 'ADMIN DATABASE';
    public $db;
    public $user;
    public $auth;

    function __construct() {
        parent::__construct();

		global $db_dsn, $db_user, $db_psw, $app_uri, $title, $tab_uri;

		if (!empty($title)) {
			$this->title = $this->title . ' - ' . $title;
		}

        $this->db = \Atk4\Data\Persistence::connect($db_dsn, $db_user, $db_psw);

        $this->uiPersistence = new UiPersistence();

        $this->initLayout([\Atk4\Ui\Layout\Maestro::class]);

		$this->auth = new \Atk4\Login\Auth($this, [
			'check' => false,
			'pageExit' => $tab_uri,
			'fieldLogin' => 'login'
		]);
		try {
			$this->auth->setModel(new LoginUser($this->db));
		} catch (Exception $e) {
			$this->auth->logout();
			$this->redirect($tab_uri);
			exit;
		}

		$userCount = $this->auth->user->getModel()->executeCountQuery();

		// Header menu buttons

		$item = $this->layout->menu->addItem()->addClass('aligned right');

		if ($this->auth->user->isLoaded()) {
			\Atk4\Ui\Button::addTo($item, [
				'icon' => 'log out'
				, 'class.circular' => true
				, 'class.green' => true
				, 'content' => $this->auth->user->get('name')
			])->link(['logout' => 1]);
		} else {
			\Atk4\Ui\Button::addTo($item, [
				'icon' => 'sign in'
				, 'class.circular' => true
				, 'class.orange' => true
			])->on('click',
				\Atk4\Ui\Modal::addTo($this)->set(function (\Atk4\Ui\View $p) {
					LoginForm::addTo($p, ['auth' => $this->auth]);
				})->jsShow()
			);
		}

		\Atk4\Ui\Button::addTo($item, [
			'icon' => 'home'
			, 'class.circular' => true
		])->on('click', $this->jsRedirect($app_uri . '..', false));

		\Atk4\Ui\Button::addTo($item, [
			'icon' => 'clone outline'
			, 'class.circular' => true
		])->on('click', $this->jsRedirect($tab_uri, true));

		// Left tabs

        $menu = $this->layout->addMenuGroup([
			'Monitoring'
			, 'icon'=>'chartline'
		]);
			$this->layout->addMenuItem([
				'Alerts'
				, 'icon'=>'bell'
			], [$app_uri . 'tab/monitoring-last-alerts'], $menu);
			$this->layout->addMenuItem([
				'Metrics'
				, 'icon'=>'tachometer alternate'
			], [$app_uri . 'tab/monitoring-last-metrics'], $menu);
			$this->layout->addMenuItem([
				'Targets'
				, 'icon'=>'crosshairs'
			], [$app_uri . 'tab/monitoring-targets'], $menu);
			$this->layout->addMenuItem([
				'Scripts'
				, 'icon'=>'file medical alternate'
			], [$app_uri . 'tab/monitoring-scripts'], $menu);
			$this->layout->addMenuItem([
				'Types'
				, 'icon'=>'microscope'
			], [$app_uri . 'tab/monitoring-types'], $menu);
			$this->layout->addMenuItem([
				'Instances'
				, 'icon'=>'server'
			], [$app_uri . 'tab/monitoring-instances'], $menu);
		/*
        $menu = $this->layout->addMenuGroup([
			'Network'
			, 'icon'=>'sitemap'
		]);
			$this->layout->addMenuItem([
				'IP Addresses'
				, 'icon'=>'ellipsis horizontal'
			], [$app_uri . 'tab/ip'], $menu);
			$this->layout->addMenuItem([
				'Scripts'
				, 'icon'=>'file medical alternate'
			], [$app_uri . 'tab/scripts'], $menu);
        */
		if ($this->auth->user->isLoaded()
			|| $userCount == 0
		) {
			$menu = $this->layout->addMenuGroup([
				'Administration'
				, 'icon'=>'tools'
			]);
				if ($this->auth->user->isLoaded()) {
					$this->layout->addMenuItem([
						'Site'
						, 'icon'=>'code'
					], [$app_uri . 'tab/admin-site'], $menu);
				}
				/*
				$this->layout->addMenuItem([
					'Export'
					, 'icon'=>'file export'
				], [$app_uri . 'tab/admin-export'], $menu);
				$this->layout->addMenuItem([
					'Database'
					, 'icon'=>'database'
				], [$app_uri . 'tab/admin-database'], $menu);
				*/
				$this->layout->addMenuItem([
					'Users'
					, 'icon'=>'user friends'
				], [$app_uri . 'tab/admin-users'], $menu);

		}
    }
}

?>
