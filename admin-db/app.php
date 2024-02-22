<?php

require_once __DIR__ . '/../vendor/autoload.php';


$app_url = '/' . basename(__DIR__) . '/';

require_once 'db.php';

foreach (glob(__DIR__ . '/model/*.php') as $file) {
    require_once $file;
}

class AdminDbApp extends \Atk4\Ui\App {
    public $title = 'ADMIN DATABASE';
    public $db;
    public $user;

    function __construct() {
        parent::__construct();

		global $db_dsn, $db_user, $db_psw, $app_url;
        
        $this->db = \Atk4\Data\Persistence::connect($db_dsn, $db_user, $db_psw);
       
        $this->initLayout([\Atk4\Ui\Layout\Maestro::class]);

        $this->layout->menuLeft->addItem(['Monitoring Scripts', 'icon'=>'chartline'], [$app_url . 'tab/scripts']);
        $this->layout->menuLeft->addItem(['IP Addresses', 'icon'=>'sitemap'], [$app_url . 'tab/ip']);
    }
}

$app = new AdminDbApp();

?>