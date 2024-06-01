<?php
declare(strict_types=1);
namespace ApiVacations\Model\User;

use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Helpers\Logger;

class UserModel extends AbstractModel
{
    public function getUsers(?array $params, string $token, string $authorize): array
    {
        $token = $this->checkToken($token);
        if ($authorize !== 'admin' OR !$this->isAdmin($token)) {
            http_response_code(401);
            throw new AppException('Unauthorized', 401);            
        }
        $group_id = $this->getUserGroupId($token);
        $result = [];
        $sql = "
            SELECT id, isActive, isAdmin, createdAt, updatedAt  
            FROM Users 
            WHERE group_id = :group_id AND isAdmin = 0
            LIMIT 
        " . ($params['offset'] ?? 0) . ", " . ($params['limit'] ?? 10);
        $params = [
            [
                'key' => ':group_id',
                'value' => $group_id,
                'type' => \PDO::PARAM_INT,
            ]
        ];
        $rows = $this->db->selectProcess($sql, $params, 'fetchAll');
        foreach($rows as $row) {
            $row['userData'] = $this->getUserData((int) $row['id']);
            $result[] = $row;            
        }
        return $result;
    }

    public function getUser(int $id, string $token, string $authorize): ?array
    {
        $canIenter = false;
        $token = $this->checkToken($token);
        if ($authorize === 'admin') {
            if ($this->isItMyUser($token, $id) OR $this->isItMe($token, $id)) {
                $canIenter = true;
            } 
        }
        if ($authorize === 'user') {
            if ($this->isItMe($token, $id)) {
                $canIenter = true;
            }
        }
        if (!$canIenter) {
            http_response_code(401);
            throw new AppException('Unauthorized - you are not Admin or User', 401);
        }
        $sql = "
            SELECT id, isActive, isAdmin, createdAt, updatedAt 
            FROM Users 
            WHERE id = :id
        ";
        $params = [
            [
                'key' => ':id',
                'value' => $id,
                'type' => \PDO::PARAM_INT,
            ]
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        if ($row) {
            $row['userData'] = $this->getUserData((int) $row['id']);
            return $row;
        }
        return null;        
    }

    public function addUser(?object $data): ?object
    {
        //User
        if ($data === null) {
            http_response_code(422);
            throw new AppException('Empty data', 422);
        }
        $this->user->setIsAdmin($data->isAdmin ?? true);
        if (!$this->user->getIsAdmin()) {
            $this->user->setGroupId((int) ($data->group_id ?? 0));
            $this->user->setIsActive(false);
        } else {
            $this->user->setIsActive(true);
        }
        $this->user->setLogin((string) ($data->login ?? ''));
        $this->user->setPass((string) ($data->pass ?? ''));
        $this->user->setTokenApi(md5(
            $this->user->getLogin() .$this->user->getPass()
        ));

        //UserData
        $this->userData->setFirstName((string) ($data->data->firstName ?? ''));
        $this->userData->setLastName((string) ($data->data->lastName ??''));
        $this->userData->setAddress((string) ($data->data->address ??''));
        $this->userData->setPostalCode((string) ($data->data->postalCode ?? ''));
        $this->userData->setCity((string) ($data->data->city ?? ''));
        $this->userData->setPhone((string) ($data->data->phone ?? ''));
        $this->userData->setEmail((string) ($data->data->email ?? ''));

        //Group
        if ($this->user->getIsAdmin()) {
            $this->group->setName((string) ($data->group->name ?? ''));
            $this->group->setAddress((string) ($data->group->address ?? ''));
            $this->group->setPostalCode((string) ($data->group->postalCode ?? ''));
            $this->group->setCity((string)  ($data->group->city ?? ''));
            $this->group->setNip((string) ($data->group->nip ?? ''));
        }

        
        //Sprawdzenie czy istnieje podana grupa jeżeli jest tworzony user
        if (!$this->user->getIsAdmin()) {
            if (!$this->isGroupId($this->user->getGroupId())) {
                http_response_code(422);
                throw new AppException('Selected group not exist', 422);
            }
        }
       
        //Sprawdzenie czy admin nie tworzy już istniejącej grupy
        if ($this->user->getIsAdmin()) {
            if ($this->isGroupNip($this->group->getNip())) {
                http_response_code(422);
                throw new AppException('Group with NIP: ' . $this->group->getNip() . ' exist' , 422);                
            }
        }

        //Sprawdzenie czy login już jest wykorzystany
        if ($this->isUserLogin($this->group->getLogin())) {
            http_response_code(422);
            throw new AppException('Login: ' . $this->group->getLogin() . ' exist' , 422);                
        }
        exit();
        


        /*
        {
            "login": "ada23m,
            "pass": "dupablada",
            "group_id": null,
            "data": {
                "firstName": "Adam",
                "lastName": "Wilk",
                "address": "Czarcia 5",
                "postalCode": "11-111",
                "city": "Old Town",
                "phone": "12-333-444-555",
                "email": "adam@aa.com"
            },
            "group": {
                "name": "F.H. Vip",
                "address": "Dzwonkowa 4",
                "postalCode": "11-222",
                "city": "New Town",
                "nip": "123-456-78-90"
            }
        }
{"login":"ada23m","pass":"dupablada","group_id":"null","data":{"firstName":"Adam","lastName":"Wilk","address":"Czarcia 5","postalCode":"11-111","city":"Old Town","phone":"12-333-444-555","email":"adam@aa.com"},"group":{"name":"F.H. Vip","address":"Dzwonkowa 4","postalCode":"11-222","city":"New Town","nip":"123-456-78-90"}}
        */
        return new \StdClass;
    }


}