<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Controller\AbstractController;
use ApiVacations\Debug\Dump;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Helpers\Request;
use ApiVacations\Model\Auth\AuthModel;
use ApiVacations\Model\User\UserModel;
use ApiVacations\Model\Group\GroupModel;
use ApiVacations\Model\Event\EventModel;
class ApiController extends AbstractController
{
    private AuthModel $authModel;
    private UserModel $userModel;
    private GroupModel $groupModel;
    private EventModel $eventModel;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->authModel = new AuthModel;
        $this->userModel = new UserModel;
        $this->groupModel = new GroupModel;
        $this->eventModel = new EventModel;
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

        switch($path) {
            case 'users':
                $this->UsersControler($rawData, $method, $token, $param, $params);
                break;
            case 'groups':
                $this->GroupsControler($rawData, $method, $token, $param, $params);
                break;
            case 'events':
                $this->EventsControler($rawData, $method, $token, $param, $params);
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
                    http_response_code(404);
                    throw new AppException('Not found', 404);
                }
            } else {
                $result['response'] = $this->userModel->getUsers(
                    'admin'
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
            header('Allow: GET,POST,PATCH, DELETE');
            http_response_code(405);
            throw new AppException('Method not allowed', 405);
        }
        http_response_code($result['code']);
        $result['status'] = 'OK';
        echo json_encode($result, JSON_UNESCAPED_SLASHES , JSON_UNESCAPED_UNICODE);
    }

    private function GroupsControler(
        ?object $rawData, string $method, ?string $token, $param=null, $params=[]
    ): void
    {
        $result = [];
        if ($method === 'GET') {
            $authorize = $this->authModel->getAuthorize($token, ['admin', 'user']);
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $result['response'] = $this->groupModel->getGroup(
                    $param, $token, $authorize
                );
                if ($result['response'] === null) {
                    http_response_code(404);
                    throw new AppException('Not found', 404);
                }
            } else {
                $result['response'] = $this->groupModel->getGroups(
                    $params, $token, $authorize
                );
            }
            $result['code'] = 200;
        } elseif ($method === 'PATCH') {
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $result['response'] = $this->groupModel->editGroup($rawData, $token, 'admin', $param); 
            } else {
                http_response_code(404);
                throw new AppException('Not found', 404);               
            }
        } else {
            header('Allow: GET, PATCH');
            http_response_code(405);
            throw new AppException('Method not allowed', 405);
        }        
        http_response_code(200);
        $result['status'] = 'OK';
        echo json_encode($result, JSON_UNESCAPED_SLASHES , JSON_UNESCAPED_UNICODE);
    }

    private function EventsControler(
        ?object $rawData, string $method, ?string $token, $param=null, $params=[]
    ): void
    {
        $result = [];
        if ($method === 'GET') {
            $authorize = $this->authModel->getAuthorize($token, ['admin', 'user']);
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $result['response'] = $this->eventModel->getEvent(
                    $param, $token, $authorize
                );
                if ($result['response'] === null) {
                    http_response_code(404);
                    throw new AppException('Not found', 404);
                }
            } else {
                $result['response'] = $this->eventModel->getEvents(
                    $params, $token, $authorize
                );
            }
            $result['code'] = 200;
        } elseif ($method === 'POST') {
            $result['response'] = $this->eventModel->addEvent(
                $rawData, $token, 'user'
            );
            $result['code'] = 201;
        } elseif ($method === 'PATCH') {

        } elseif ($method === 'DELETE') {

        } else {
            header('Allow: GET,POST,PATCH, DELETE');
            http_response_code(405);
            throw new AppException('Method not allowed', 405);
        }
        http_response_code($result['code']);
        $result['status'] = 'OK';
        echo json_encode($result, JSON_UNESCAPED_SLASHES , JSON_UNESCAPED_UNICODE);
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