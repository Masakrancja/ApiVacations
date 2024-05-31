<?php
declare(strict_types= 1);
namespace ApiVacations\Model;

use ApiVacations\Config\DBConfig;
use ApiVacations\Helpers\DB;
use ApiVacations\Exceptions\AppException;

abstract class AbstractModel
{
    protected DB $db;

    public function __construct()
    {
        $this->db = DB::getInstance(DBConfig::getConfig());
    }

    protected function getUserData(int $id): array
    {
        $sql = "
            SELECT firstName, lastName, address, postalCode, city, email 
            FROM UserData 
            WHERE user_id = :id
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

    protected function getUserId(string $token): int
    {
        $sql = "SELECT id from Users WHERE token_api = :token";
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
        http_response_code(401);
        throw new AppException('Unauthorized', 401);
    }

    protected function getUserGroupId(string $token): int
    {
        $sql = "SELECT group_id from Users WHERE token_api = :token";
        $params = [
            [
                'key' => ':token',
                'value' => $token,
                'type' => \PDO::PARAM_STR,
            ]
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        if ($row) {
            return (int) $row['group_id'];
        }
        http_response_code(401);
        throw new AppException('Unauthorized', 401);
    }

    protected function isItMe(string $token, int $id): bool
    {
        $sql = "
            SELECT id 
            FROM Users 
            WHERE token_api = :token AND id = :id
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
        $group_id = $this->getUserGroupId($token);
        $sql = "
            SELECT id 
            FROM Users 
            WHERE group_id = :group_id AND id = :id AND isAdmin = false
        ";
        $params = [
            [
                'key' => ':group_id',
                'value' => $group_id,
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
            WHERE token_api = :token AND isAdmin = true
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
}