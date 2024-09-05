<?php
declare(strict_types=1);
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
        $userId = $this->getUserId($token);
        $sql = "SELECT groupId from Users WHERE id = :userId";
        $params = [
            [
                'key' => ':userId',
                'value' => $userId,
                'type' => \PDO::PARAM_INT
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
        $userId = $this->getUserId($token);
        return $userId === $id;
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
        $userId = $this->getUserId($token);
        $sql = "
            SELECT id 
            FROM Users 
            WHERE id = :userId AND isAdmin = true
        ";
        $params = [
            [
                'key' => ':userId',
                'value' => $userId,
                'type' => \PDO::PARAM_INT
            ]
        ];
        return (bool) $this->db->selectProcess($sql, $params, 'fetch');
    }

    protected function isUserActive(string $token): bool
    {
        $userId = $this->getUserId($token);
        $sql = "
            SELECT id 
            FROM Users 
            WHERE id = :userId AND isActive = true
        ";
        $params = [
            [
                'key' => ':userId',
                'value' => $userId,
                'type' => \PDO::PARAM_INT,
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
                "key" => ":id",
                "value" => $id,
                "type" => \PDO::PARAM_INT,
            ],
        ];
        $row = $this->db->selectProcess($sql, $params, "fetch");
        if ($row) {
            return $id;
        }
        http_response_code(422);
        throw new AppException('Niepoprawny powód urlopu', 422);
    }

    protected function getUserId(string $token): ?int
    {
        if (!$this->isTokenValid($token)) {
            return null;
        }
        $sql = "
            SELECT userId 
            FROM Tokens 
            WHERE token = :token
        ";
        $params = [
            [
                "key" => ":token",
                "value" => $token,
                "type" => \PDO::PARAM_STR,
            ],
        ];
        $row = $this->db->selectProcess($sql, $params, "fetch");
        if ($row) {
            return $row['userId'];
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }

    protected function isTokenValid(?string $token): bool
    {
        if ($token) {
            $sql = "
                SELECT validAt 
                FROM Tokens
                WHERE token = :token
            ";
            $params = [
                [
                    "key" => ":token",
                    "value" => $token,
                    "type" => \PDO::PARAM_STR,
                ],
            ];
            $row = $this->db->selectProcess($sql, $params, "fetch");
            if ($row) {
                $date = Date("Y-m-d H:i:s");
                if ($row['validAt'] >= $date) {
                    return true;
                }
            }
        }
        return false;
    }


}