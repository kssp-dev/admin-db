<?php

class AdminDbApp extends \Atk4\Ui\App {
    public $title = 'ADMIN DATABASE';
    public $db;
    public $user;

    function __construct() {
        parent::__construct();

		global $db_dsn, $db_user, $db_psw, $app_uri, $title, $tab_uri;
		
		if (!empty($title)) {
			$this->title = $this->title . ' - ' . $title;
		}
        
        $this->db = \Atk4\Data\Persistence::connect($db_dsn, $db_user, $db_psw);
        
        $this->uiPersistence = new UiPersistence();
       
        $this->initLayout([\Atk4\Ui\Layout\Maestro::class]);
		
		// Header menu buttons
		
		$item = $this->layout->menu->addItem()->addClass('aligned right');
		
		\Atk4\Ui\Button::addTo($item, [
			'icon' => 'home'
			, 'class.circular' => true
		])	->on('click', $this->jsRedirect($app_uri . '..', false));
		
		\Atk4\Ui\Button::addTo($item, [
			'icon' => 'clone outline'
			, 'class.circular' => true
		])	->on('click', $this->jsRedirect($tab_uri, true));
		
		// Left tabs

        $menu = $this->layout->addMenuGroup([
			'Monitoring'
			, 'icon'=>'chart bar'
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
        
        $menu = $this->layout->addMenuGroup([
			'Administration'
			, 'icon'=>'tools'
		]);
			$this->layout->addMenuItem([
				'Site'
				, 'icon'=>'code'
			], [$app_uri . 'tab/admin-site'], $menu);
			$this->layout->addMenuItem([
				'Export'
				, 'icon'=>'file export'
			], [$app_uri . 'tab/admin-export'], $menu);
			$this->layout->addMenuItem([
				'Database'
				, 'icon'=>'database'
			], [$app_uri . 'tab/admin-database'], $menu);
    }
}

?>
