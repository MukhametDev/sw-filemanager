<?php

namespace App;

use App\DB\Database;

class App
{
    private $db;
    public function __construct($db)
    {
        $this->db = $db;
        $this->db->init();
        $this->loadRoutes();
    }

    public function run()
    {
        return $this->db->getConnection();
    }

    protected function loadRoutes()
    {
        // Подключение файлов с маршрутами
        require_once __DIR__ . '/Routes/web.php';
    }
}
