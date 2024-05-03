<?php
declare(strict_types= 1);
namespace ApiVacations\Controller;

use ApiVacations\Controller\AbstractController;
use ApiVacations\Debug\Dump;

class App extends AbstractController
{
    public function run()
    {
        Dump::dump($this->db);
        Dump::dump($this->request);
    }
}