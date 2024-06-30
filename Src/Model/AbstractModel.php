<?php
declare(strict_types= 1);
namespace ApiVacations\Model;

use ApiVacations\Config\DBConfig;
use ApiVacations\Helpers\DB;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Model\User\User;
use ApiVacations\Model\User\UserData;
use ApiVacations\Model\Group\Group;
use ApiVacations\Model\Event\Event;
use ApiVacations\Model\Reason\Reason;

abstract class AbstractModel
{
    protected DB $db;
    protected User $user;
    protected UserData $userData;
    protected Group $group;
    protected Event $event;
    protected Reason $reason;


    public function __construct()
    {
        $this->db = DB::getInstance(DBConfig::getConfig());
        $this->user = new User;
        $this->userData = new UserData;
        $this->group = new Group;
        $this->event = new Event;
        $this->reason = new Reason;
    }

    protected function getUserId(string $token): int
    {
        $sql = "SELECT id from Users WHERE tokenApi = :token";
        $params = [
            [
                'key' => ':token',
                'value' => $token,
                'type' => \PDO::PARAM_STR,
            ]
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        if ($row) {
            return (int) $row['id'];
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }

    protected function isGroupId(int $id): bool
    {
        $sql = "SELECT id FROM `Groups` WHERE id = :id";
        $params = [
            [
                'key' => ':id',
                'value' => $id,
                'type' => \PDO::PARAM_INT,
            ]
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        return (bool) $row;
    }

    protected function isGroupNip(string $nip): bool
    {
        $sql = "SELECT id FROM `Groups` WHERE nip = :nip";
        $params = [
            [
                'key' => ':nip',
                'value' => $nip,
                'type' => \PDO::PARAM_STR,
            ]
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        return (bool) $row;        
    }

    protected function isUserLogin(string $login): bool
    {
        $sql = "SELECT id FROM Users WHERE login = :login";
        $params = [
            [
                'key' => ':login',
                'value' => $login,
                'type' => \PDO::PARAM_STR,
            ]
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        return (bool) $row;  
    }

    protected function getUserGroupId(string $token): int
    {
        $sql = "SELECT groupId from Users WHERE tokenApi = :token";
        $params = [
            [
                'key' => ':token',
                'value' => $token,
                'type' => \PDO::PARAM_STR,
            ]
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        if ($row) {
            return (int) $row['groupId'];
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }

    protected function isItMe(string $token, int $id): bool
    {
        $sql = "
            SELECT id 
            FROM Users 
            WHERE tokenApi = :token AND id = :id
        ";
        $params = [
            [
                'key' => ':token',
                'value' => $token,
                'type' => \PDO::PARAM_STR,
            ],
            [
                'key' => ':id',
                'value' => $id,
                'type' => \PDO::PARAM_INT,
            ],
        ];
        return (bool) $this->db->selectProcess($sql, $params, 'fetch');
    }

    protected function isItMyUser(string $token, int $id): bool
    {
        $groupId = $this->getUserGroupId($token);
        $sql = "
            SELECT id 
            FROM Users 
            WHERE groupId = :groupId AND id = :id AND isAdmin = false
        ";
        $params = [
            [
                'key' => ':groupId',
                'value' => $groupId,
                'type' => \PDO::PARAM_INT,
            ],
            [
                'key' => ':id',
                'value' => $id,
                'type' => \PDO::PARAM_INT,
            ]
        ];
        return (bool) $this->db->selectProcess($sql, $params, 'fetch');
    }

    protected function isAdmin(string $token): bool
    {
        $sql = "
            SELECT id 
            FROM Users 
            WHERE tokenApi = :token AND isAdmin = true
        ";
        $params = [
            [
                'key' => ':token',
                'value' => $token,
                'type' => \PDO::PARAM_STR,
            ]
        ];
        return (bool) $this->db->selectProcess($sql, $params, 'fetch');
    }

    protected function isUserActive(string $token): bool
    {
        $sql = "
        SELECT id 
        FROM Users 
        WHERE tokenApi = :token AND isActive = true
    ";
    $params = [
        [
            'key' => ':token',
            'value' => $token,
            'type' => \PDO::PARAM_STR,
        ]
    ];
    return (bool) $this->db->selectProcess($sql, $params, 'fetch');       
    }

    protected function checkReason(int $id): int
    {
        $sql = "
            SELECT id
            FROM Reasons
            WHERE id = :id
        ";
        $params = [
            [
                "key"=> ":id",
                "value" => $id,
                "type"=> \PDO::PARAM_INT,
            ],
        ];
        $row =  $this->db->selectProcess($sql, $params, "fetch");
        if ($row) {
            return $id;
        }
        http_response_code(422);
        throw new AppException('Niepoprawny powód urlopu', 422);        
    }

}