<?php
declare(strict_types=1);
namespace ApiVacations\Model\User;

use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Helpers\Logger;

class UserModel extends AbstractModel
{
    /**
     * Get particulary all users from Database. Default first 10 users
     *
     * @param array|null $params // keys: int limit, int offset
     * @param string $token // X-API-KEY token
     * @param string $authorize // 'admin' or 'user'
     * @return array
     */
    public function getUsers(?array $params, string $token, string $authorize): array
    {
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

    /**
     * Get particular user from database by ID
     *
     * @param integer $id // User ID
     * @param string $token // X-API-KEY token
     * @param string $authorize // 'admin' or 'user'
     * @return array|null
     */
    public function getUser(int $id, string $token, string $authorize): ?array
    {
        $canIenter = false;
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

    /**
     * Add User to Database
     *
     * @param object|null $data
     * @return object|null
     */
    public function addUser(?object $data): ?object
    {
        $result = new \StdClass;
        //User
        if ($data === null) {
            http_response_code(422);
            throw new AppException('Empty data', 422);
        }
        $this->user->setIsAdmin((bool) ($data->isAdmin ?? true));
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
        if ($this->isUserLogin($this->user->getLogin())) {
            http_response_code(422);
            throw new AppException('Login: ' . $this->user->getLogin() . ' exist' , 422);                
        }
        try {
            $this->db->getConn()->beginTransaction();
            $sql = "
                INSERT INTO Users (login, pass, token_api, isActive, isAdmin)
                VALUES (:login, :pass, :token_api, :isActive, :isAdmin)
            ";
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(':login', $this->user->getLogin(), \PDO::PARAM_STR);
            $stmt->bindValue(':pass', md5($this->user->getPass()), \PDO::PARAM_STR);
            $stmt->bindValue(':token_api', $this->user->getTokenApi(), \PDO::PARAM_STR);
            $stmt->bindValue(':isActive', $this->user->getIsActive(), \PDO::PARAM_BOOL);
            $stmt->bindValue(':isAdmin', $this->user->getIsAdmin(), \PDO::PARAM_BOOL);
            $stmt->execute();
            $userId = $this->db->getConn()->lastInsertId();
            $sql = "
                INSERT INTO UserData (user_id, firstName, lastName, address, postalCode, city, phone, email) 
                VALUES (:user_id, :firstName, :lastName, :address, :postalCode, :city, :phone, :email)
            ";
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':firstName', $this->userData->getFirstName(), \PDO::PARAM_STR);
            $stmt->bindValue(':lastName', $this->userData->getLastName(), \PDO::PARAM_STR);
            $stmt->bindValue(':address', $this->userData->getAddress(), \PDO::PARAM_STR);
            $stmt->bindValue(':postalCode', $this->userData->getPostalCode(), \PDO::PARAM_STR);
            $stmt->bindValue(':city', $this->userData->getCity(), \PDO::PARAM_STR);
            $stmt->bindValue(':phone', $this->userData->getPhone(), \PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->userData->getEmail(), \PDO::PARAM_STR);
            $stmt->execute();

            if ($this->user->getIsAdmin()) {
                $sql = "
                    INSERT INTO Groups (user_id, name, address, postalCode, city, nip) 
                    VALUES (:user_id, :name, :address, :postalCode, :city, :nip)
                ";
                $stmt = $this->db->getConn()->prepare($sql);
                $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':name', $this->group->getName(), \PDO::PARAM_STR);
                $stmt->bindValue(':address', $this->group->getAddress(), \PDO::PARAM_STR);
                $stmt->bindValue(':postalCode', $this->group->getPostalCode(), \PDO::PARAM_STR);
                $stmt->bindValue(':city', $this->group->getCity(), \PDO::PARAM_STR);
                $stmt->bindValue(':nip', $this->group->getNip(), \PDO::PARAM_STR);
                $stmt->execute();
                $groupId = $this->db->getConn()->lastInsertId();
            } else {
                $groupId = $this->user->getGroupId();
            }
            $sql = "
                UPDATE Users SET group_id = :group_id WHERE id = :id
            ";
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(':group_id', $groupId, \PDO::PARAM_INT);
            $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $this->db->getConn()->commit();
        }
        catch (\PDOException $e) {
            $this->db->getConn()->rollBack();
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }

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
        return $result;
    }


}