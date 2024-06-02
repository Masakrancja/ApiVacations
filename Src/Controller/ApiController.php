<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Controller\AbstractController;
use ApiVacations\Debug\Dump;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Helpers\Request;
use ApiVacations\Model\Auth\AuthModel;
use ApiVacations\Model\User\UserModel;
class ApiController extends AbstractController
{
    private UserModel $userModel;
    private AuthModel $authModel;
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->authModel = new AuthModel;
        $this->userModel = new UserModel;
    }

    public function run(): void
    {
        [$path, $param] = $this->request->getUriTable();
        $method = $this->request->getMethod();
        $token = null;
        if (!($method === 'POST' AND $path === 'users')) {
            $token = $this->authModel->checkToken(
                $this->authModel->getToken()
            );
        }

        $params = $this->request->getParams();
        $rawData = $this->request->getRawData();

        //http api.vacations.local/users?limit=100\&offset=1 X-API-KEY:wfsdfasdfdf name=JAN

        switch($path) {
            case 'users':
                $this->UsersControler($rawData, $method, $token, $param, $params);
                break;
            case 'events':
                $this->EventsControler();
                break;
            case 'reasons':
                $this->ReasonsControler();
                break;   
            case 'auth':
                $this->AuthControler();
                break;
            default:
                http_response_code(404);
                throw new AppException('Not Found', 404);
        }
    }

    private function UsersControler(
        ?object $rawData, string $method, ?string $token, $param=null, $params=[]
    ): void
    {
        $result = [];
        if ($method === 'GET') {
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $authorize = $this->authModel->getAuthorize($token, ['admin', 'user']);
                $result['response'] = $this->userModel->getUser(
                    $param, $token, $authorize
                );
                if ($result['response'] === null) {
                    http_response_code(200);
                    throw new AppException('Not found', 404);
                }
            } else {
                $result['response'] = $this->userModel->getUsers(
                    $params, $token, 'admin'
                );
            }
            $result['code'] = 200;
        } elseif ($method === 'POST') {
            $result['response'] = $this->userModel->addUser($rawData);
            $result['code'] = 201;
        } elseif ($method === 'PATCH') {
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $authorize = $this->authModel->getAuthorize($token, ['admin', 'user']);  
                $result['response'] = $this->userModel->editUserData($rawData, $token, $authorize, $param); 
            } else {
                http_response_code(404);
                throw new AppException('Not found', 404);               
            }
            $result['code'] = 200;
        } elseif ($method === 'DELETE') { 
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $this->userModel->deleteUser($token, 'admin', $param); 
            } else {
                http_response_code(404);
                throw new AppException('Not found', 404);               
            }
            $result['code'] = 204;
        } else {
            header('Allow: GET,POST,PATCH');
            http_response_code(405);
            throw new AppException('Method not allowed', 405);
        }
        http_response_code($result['code']);
        $result['status'] = 'OK';
        echo json_encode($result, JSON_UNESCAPED_SLASHES , JSON_UNESCAPED_UNICODE);
    }

    private function EventsControler()
    {
        echo '{"events":"events"}' . "\n";
    }

    private function ReasonsControler()
    {
        echo '{"Reasons":"Reasons"}' . "\n";
    }

    private function AuthControler()
    {
        echo '{"Auth":"Auth"}' . "\n";
    }
}