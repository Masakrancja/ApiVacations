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
        $groupId = $this->getUserGroupId($token);
        return $this->getUsersFromDB($params, $groupId);
    }

    /**
     * Get particular user
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
            throw new AppException('Unauthorized', 401);
        }
        return $this->getUserFromDB($id);
    }

    /**
     * Add User to Database
     *
     * @param object|null $data
     * @return object|null
     */
    public function addUser(?object $data): ?array
    {
        $result = new \StdClass;

        //User
        if ($data === null) {
            http_response_code(422);
            throw new AppException('Empty data', 422);
        }
        $this->user->setIsAdmin((bool) ($data->isAdmin ?? true));
        if (!$this->user->getIsAdmin()) {
            $this->user->setGroupId((int) ($data->groupId ?? 0));
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
        $this->userData->setFirstName((string) ($data->userData->firstName ?? ''));
        $this->userData->setLastName((string) ($data->userData->lastName ??''));
        $this->userData->setAddress((string) ($data->userData->address ??''));
        $this->userData->setPostalCode((string) ($data->userData->postalCode ?? ''));
        $this->userData->setCity((string) ($data->userData->city ?? ''));
        $this->userData->setPhone((string) ($data->userData->phone ?? ''));
        $this->userData->setEmail((string) ($data->userData->email ?? ''));

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
        
        $userId = $this->addUserToDB();
        return $this->getUserFromDB($userId);


        /*
        {
            "login": "ada23m,
            "pass": "dupablada",
            "groupId": null,
            "userData": {
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
{"login":"ada23m","pass":"dupablada","groupId":"null","data":{"firstName":"Adam","lastName":"Wilk","address":"Czarcia 5","postalCode":"11-111","city":"Old Town","phone":"12-333-444-555","email":"adam@aa.com"},"group":{"name":"F.H. Vip","address":"Dzwonkowa 4","postalCode":"11-222","city":"New Town","nip":"123-456-78-90"}}
        */
        
    }

    public function editUserData(
        ?object $data, string $token, string $authorize, int $id
    ): ?array
    {
        echo json_encode($data) . "\n\n";

        $user = $this->getUser($id, $token, $authorize);
        if (!$user) {
            http_response_code(200);
            throw new AppException('Not found', 404);
        }

        echo json_encode($user) . "\n\n";

        $this->userData->setFirstName((string) ($data->firstName ?? $user['userData']['firstName']));
        $this->userData->setLastName((string) ($data->lastName ?? $user['userData']['lastName']));
        $this->userData->setAddress((string) ($data->address ?? $user['userData']['address']));
        $this->userData->setPostalCode((string) ($data->postalCode ?? $user['userData']['postalCode']));
        $this->userData->setCity((string) ($data->city ?? $user['userData']['city']));
        $this->userData->setPhone((string) ($data->phone ?? $user['userData']['phone']));
        $this->userData->setEmail((string) ($data->email ?? $user['userData']['email']));
        $this->user->setId((int) $user['id']);
        $this->user->setIsActive((bool) ($data->isActive ?? $user['isActive']));

        $rowCount = $this->editUserInDB($id);
        return $this->getUserFromDB($id);
                



        /*
        {
            "firstName":"",
            "lastName":"",
            "address":"",
            "postalCode":"",
            "city":"",
            "phone":"",
            "email":""
        }
        */
    }

    public function deleteUser(string $token, string $authorize, int $id)
    {
        if ($authorize !== 'admin' OR !$this->isAdmin($token)) {
            http_response_code(401);
            throw new AppException('Unauthorized', 401);            
        }
        if ($this->isItMyUser($token, $id)) {
            return $this->deleteUserFromDB($id);
        } 
        http_response_code(401);
        throw new AppException('Unauthorized', 401);        
    }

    private function getUsersFromDB(?array $params, int $groupId): array
    {
        $result = [];
        $sql = "
            SELECT id, login, isActive, isAdmin, createdAt, updatedAt  
            FROM Users 
            WHERE groupId = :groupId
            LIMIT 
        " . ($params['offset'] ?? 0) . ", " . ($params['limit'] ?? 10);
        $params = [
            [
                'key' => ':groupId',
                'value' => $groupId,
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

    private function getUserData(int $id): array
    {
        $sql = "
            SELECT firstName, lastName, address, postalCode, city, phone, email, createdAt, updatedAt  
            FROM UserData 
            WHERE userId = :id
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
            return $row;
        }
        http_response_code(401);
        throw new AppException('Unauthorized', 401);
    }

    private function getUserFromDB(int $id): ?array
    {
        $sql = "
            SELECT id, login, isActive, isAdmin, createdAt, updatedAt 
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

    private function addUserToDB(): int
    {
        try {
            $this->db->getConn()->beginTransaction();
            $sql = "
                INSERT INTO Users (login, pass, tokenApi, isActive, isAdmin)
                VALUES (:login, :pass, :tokenApi, :isActive, :isAdmin)
            ";
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(':login', $this->user->getLogin(), \PDO::PARAM_STR);
            $stmt->bindValue(':pass', md5($this->user->getPass()), \PDO::PARAM_STR);
            $stmt->bindValue(':tokenApi', $this->user->getTokenApi(), \PDO::PARAM_STR);
            $stmt->bindValue(':isActive', $this->user->getIsActive(), \PDO::PARAM_BOOL);
            $stmt->bindValue(':isAdmin', $this->user->getIsAdmin(), \PDO::PARAM_BOOL);
            $stmt->execute();
            $userId = $this->db->getConn()->lastInsertId();
            $sql = "
                INSERT INTO UserData (userId, firstName, lastName, address, postalCode, city, phone, email) 
                VALUES (:userId, :firstName, :lastName, :address, :postalCode, :city, :phone, :email)
            ";
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
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
                    INSERT INTO `Groups` (userId, name, address, postalCode, city, nip) 
                    VALUES (:userId, :name, :address, :postalCode, :city, :nip)
                ";
                $stmt = $this->db->getConn()->prepare($sql);
                $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
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
                UPDATE Users SET groupId = :groupId WHERE id = :id
            ";
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(':groupId', $groupId, \PDO::PARAM_INT);
            $stmt->bindValue(':id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            $this->db->getConn()->commit();
            return (int) $userId;
        }
        catch (\PDOException $e) {
            $this->db->getConn()->rollBack();
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }

    private function editUserInDB(int $id): int
    {
        try {
            $this->db->getConn()->beginTransaction();
            $sql = "
                UPDATE UserData 
                SET 
                    firstName = :firstName, 
                    lastName = :lastName, 
                    address = :address, 
                    postalCode = :postalCode, 
                    city = :city, 
                    phone = :phone, 
                    email = :email
                WHERE userId = :id
            ";
            $params = [
                [
                    'key' => ':firstName',
                    'value' => $this->userData->getFirstName(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':lastName',
                    'value' => $this->userData->getLastName(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':address',
                    'value' => $this->userData->getAddress(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':postalCode',
                    'value' => $this->userData->getPostalCode(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':city',
                    'value' => $this->userData->getCity(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':phone',
                    'value' => $this->userData->getPhone(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':email',
                    'value' => $this->userData->getEmail(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':email',
                    'value' => $this->userData->getEmail(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':id',
                    'value' => $this->user->getId(),
                    'type' => \PDO::PARAM_INT,
                ],
            ];

            $stmt = $this->db->getConn()->prepare($sql);
            foreach ($params as $param) {
                $stmt->bindValue($param['key'], $param['value'], $param['type']);
            }
            $stmt->execute();

            $sql = "
                UPDATE Users 
                SET isActive = :isActive 
                WHERE id = :id
            ";
            $params = [
                [
                    'key' => ':isActive',
                    'value' => $this->user->getIsActive(),
                    'type' => \PDO::PARAM_BOOL,
                ],
                [
                    'key' => ':id',
                    'value' => $this->user->getId(),
                    'type' => \PDO::PARAM_INT,
                ],
            ];
            $stmt = $this->db->getConn()->prepare($sql);
            foreach ($params as $param) {
                $stmt->bindValue($param['key'], $param['value'], $param['type']);
            }
            $stmt->execute();

            $this->db->getConn()->commit();
            return (int) $stmt->rowCount();
        }
        catch (\PDOException $e) {
            $this->db->getConn()->rollBack();
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }

    private function deleteUserFromDB(int $id): void
    {
        $params = [
            [
                'key' => ':id',
                'value' => $id,
                'type' => \PDO::PARAM_INT,
            ],
        ];  
        try {
            $this->db->getConn()->beginTransaction();
            $sql = "DELETE FROM Events WHERE userId = :id";
            $stmt = $this->db->getConn()->prepare($sql);
            foreach ($params as $param) {
                $stmt->bindValue($param['key'], $param['value'], $param['type']);
            }
            $stmt->execute();

            $sql = "DELETE FROM UserData WHERE userId = :id";
            $stmt = $this->db->getConn()->prepare($sql);
            foreach ($params as $param) {
                $stmt->bindValue($param['key'], $param['value'], $param['type']);
            }
            $stmt->execute();

            $sql = "DELETE FROM Users WHERE id = :id";
            $stmt = $this->db->getConn()->prepare($sql);
            foreach ($params as $param) {
                $stmt->bindValue($param['key'], $param['value'], $param['type']);
            }
            $stmt->execute();
            $this->db->getConn()->commit();
        }
        catch (\PDOException $e) {
            $this->db->getConn()->rollBack();
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }
}