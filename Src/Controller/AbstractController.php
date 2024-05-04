<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Helpers\Request;
use ApiVacations\Helpers\Routes;
use ApiVacations\Helpers\Auth;
use ApiVacations\Config\DBConfig;
use ApiVacations\Helpers\DB;

abstract class AbstractController
{    
    protected Request $request;
    protected Routes $routes;
    protected Auth $auth;


    public function __construct()
    {
        $this->request = new Request();
        $this->routes = new Routes;
        $this->auth = new Auth(DB::getInstance(DBConfig::getConfig()));
    }

}