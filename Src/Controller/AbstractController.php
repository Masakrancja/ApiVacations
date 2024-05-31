<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Helpers\Request;

abstract class AbstractController
{    
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }
}