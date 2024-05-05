<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Controller\AbstractController;
use ApiVacations\Debug\Dump;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Helpers\Request;
use ApiVacations\Helpers\Routes;
use ApiVacations\Helpers\Auth;
use ApiVacations\Model\User\UserModel;
class ApiController extends AbstractController
{
    private UserModel $userModel;
    public function __construct(Request $request, Routes $routes, Auth $auth)
    {
        $this->request = $request;
        $this->routes = $routes;
        $this->auth = $auth;
        $this->userModel = new UserModel;
    }

    public function run(): string
    {
        $result = '';
        $redirectUrl = $this->request->getRedirectUrl();
        $method = $this->request->getMethod();
        $token = $this->auth->getToken();

        $rawData = $this->request->getRawData();


        echo json_encode($rawData);
        //exit();


        $result = $this->routes->getFunction($redirectUrl, $method);
        if (!empty($result)) {
            $function = $result['name'] ;
            $param0 = $result['params'][0] ?? null;
            $param1 = $result['params'][1] ?? null;
            
            if ($param0 && $param1) {
                return $this->userModel->{$function}($rawData, $param0, $param1);
            } elseif ($param0) {
                return $this->userModel->{$function}($rawData, $param0);
            } else {
                return $this->userModel->{$function}($rawData);
            }
        } else {
            throw new AppException('Not Found', 404);
        }
    }
}