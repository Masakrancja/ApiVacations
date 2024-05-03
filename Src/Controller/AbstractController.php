<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Helpers\DB;
use ApiVacations\Helpers\Request;
use ApiVacations\Config\DBConfig;


abstract class AbstractController
{    
    protected Request $request;
    protected DB $db;


    public function __construct()
    {
        $this->request = new Request();
        $this->db = DB::getInstance(DBConfig::getConfig());

        
    }

}