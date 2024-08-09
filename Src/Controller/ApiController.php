<?php
declare(strict_types=1);
namespace ApiVacations\Controller;

use ApiVacations\Controller\AbstractController;
use ApiVacations\Exceptions\AppException;
class ApiController extends AbstractController
{
    public function run(): void
    {
        [$path, $param] = $this->request->getUriTable();

        $method = $this->request->getMethod();
        $params = $this->request->getParams();
        $rawData = $this->request->getRawData();
        $token = null;

        $authorizeAllowed = true;
        if ($method === 'POST' AND $path === 'users') {
            $authorizeAllowed = false;
        }
        if ($method === 'POST' AND $path === 'auth') {
            $authorizeAllowed = false;
        }
        if ($method === 'GET' AND $path === 'groups') {
            $authorizeAllowed = false;
        }
        if ($authorizeAllowed) {
            $token = $this->authModel->checkToken(
                $this->authModel->getTokenFromHeader()
            );
        }

        switch($path) {
            case 'users':
                $this->UsersControler(
                    $rawData, $method, $token, $param, $params
                );
                break;
            case 'groups':
                $this->GroupsControler(
                    $rawData, $method, $token, $param, $params
                );
                break;
            case 'events':
                $this->EventsControler(
                    $rawData, $method, $token, $param, $params
                );
                break;
            case 'reasons':
                $this->ReasonsControler(
                    $rawData, $method, $token, $param, $params
                );
                break;   
            case 'auth':
                $this->AuthControler(
                    $rawData, $method, $token, $param, $params
                );
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
                [
                    $result['response'], 
                    $result['allRows'],
                ] = $this->userModel->getUsers(
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
                $result['response'] = $this->userModel->editUser($rawData, $token, $authorize, $param); 
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
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $result['response'] = $this->groupModel->getGroup($param);
                if ($result['response'] === null) {
                    http_response_code(404);
                    throw new AppException('Not found', 404);
                }
            } else {
                $result['response'] = $this->groupModel->getGroups();
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
                [
                    $result['response'], 
                    $result['allRows'],
                ] = $this->eventModel->getEvents(
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
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $authorize = $this->authModel->getAuthorize($token, ['admin', 'user']);  
                $result['response'] = $this->eventModel->editEvent($rawData, $token, $authorize, $param); 
            } else {
                http_response_code(404);
                throw new AppException('Not found', 404);               
            }
            $result['code'] = 200;
        } elseif ($method === 'DELETE') {
            if ($param !== null) {
                $param = $this->request->paramValidateInt($param);
                $this->eventModel->deleteEvent($token, 'user', $param); 
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

    private function ReasonsControler(
        ?object $rawData, string $method, ?string $token, $param=null, $params=[]
    ): void
    {
        $result = [];
        if ($method === 'GET') {
            $authorize = $this->authModel->getAuthorize($token, ['admin', 'user']);
            $result['response'] = $this->reasonModel->getReasons(
                $authorize
            );
            $result['code'] = 200;
        } else {
            header('Allow: GET');
            http_response_code(405);
            throw new AppException('Method not allowed', 405);
        }
        http_response_code($result['code']);
        $result['status'] = 'OK';
        echo json_encode($result, JSON_UNESCAPED_SLASHES , JSON_UNESCAPED_UNICODE);
    }

    private function AuthControler(
        ?object $rawData, string $method, ?string $token, $param=null, $params=[]
    ): void
    {
        $result = [];
        if ($method === 'POST') {
            $result['response'] = $this->authModel->createAuth(
                $rawData
            );
            $result['code'] = 201;
        } elseif ($method === 'PATCH') {
            $result['response'] = $this->authModel->refreshToken($token);
            $result['code'] = 200;   
        } elseif ($method === 'GET') {
            $result['response'] = $this->authModel->getAuth($token);
            $result['code'] = 200;
        } else {
            header('Allow: GET,POST,PATCH,OPTIONS');
            http_response_code(405);
            throw new AppException('Method not allowed', 405);
        }
        http_response_code($result['code']);
        $result['status'] = 'OK';
        echo json_encode($result, JSON_UNESCAPED_SLASHES , JSON_UNESCAPED_UNICODE);
    }
}