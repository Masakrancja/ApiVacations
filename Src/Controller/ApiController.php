<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Controller\AbstractController;
use ApiVacations\Exceptions\AppException;
class ApiController extends AbstractController
{
    public function __construct()
    {
        $redirectUrl = $this->request->getRedirectUrl();


        if ($this->request->getMethod() === "GET") {
            echo "GET";
        } elseif ($this->request->getMethod() === "POST") {
            echo "POST";
        } elseif ($this->request->getMethod() === "PUT") {
            echo "PUT";
        } elseif ($this->request->getMethod() === "DELETE") {
            echo "DELETE";
        } else {
            throw new AppException("Bad request", 400);
        }
    }
}