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

    public function addUser(
        
    ): void
    {

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
    }


}