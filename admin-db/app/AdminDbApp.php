<?php

class AdminDbApp extends \Atk4\Ui\App {
    public $title = 'ADMIN DATABASE';
    public $db;
    public $user;

    function __construct() {
        parent::__construct();

		global $db_dsn, $db_user, $db_psw, $app_uri, $title;
		
		if (!empty($title)) {
			$this->title = $this->title . ' - ' . $title;
		}
        
        $this->db = \Atk4\Data\Persistence::connect($db_dsn, $db_user, $db_psw);
       
        $this->initLayout([\Atk4\Ui\Layout\Maestro::class]);
		
		// Header menu buttons
		
		$item = $this->layout->menu->addItem()->addClass('aligned right');
		
		Atk4\Ui\Button::addTo($item, [
			'icon' => 'home'
			, 'class.circular' => true
		])	->on('click', $this->jsRedirect($app_uri . '..', false));
		
		Atk4\Ui\Button::addTo($item, [
			'icon' => 'clone outline'
			, 'class.circular' => true
		])	->on('click', $this->jsRedirect($app_uri . '..', true));
		
		// Left tabs

        $this->layout->menuLeft->addItem([
			'Monitoring Scripts'
			, 'icon'=>'chart bar'
		], [$app_uri . 'tab/scripts']);
		
        $this->layout->menuLeft->addItem([
			'IP Addresses'
			, 'icon'=>'sitemap'
		], [$app_uri . 'tab/ip']);
        
        $this->layout->menuLeft->addItem([
			'Administration'
			, 'icon'=>'tools'
		], [$app_uri . 'tab/admin']);
    }
}

?>
