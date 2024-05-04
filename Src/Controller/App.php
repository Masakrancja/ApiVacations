<?php
declare(strict_types= 1);
namespace ApiVacations\Controller;

use ApiVacations\Controller\AbstractController;
use ApiVacations\Controller\ApiController;

class App extends AbstractController
{
    public function run()
    {
        (new ApiController(
            $this->request, $this->routes, $this->auth
        ))->run();
    }
}