<?php
declare(strict_types=1);
namespace ApiVacations\Model\Auth;

use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Helpers\Logger;

class AuthModel extends AbstractModel
{
    /**
     * Create new token. Token is valid 15 min.
     *
     * @param object|null $data
     * @return array
     */
    public function createAuth(?object $data): array
    {
        date_default_timezone_set('Europe/Warsaw');
        $login = $data->login ?? null;
        $pass = $data->pass ?? null;
        if ($login && $pass) {
            $this->user->setLogin((string) $login);
            $this->user->setPass((string) $pass);
            $result = $this->getAuthData($login, $pass);
            if ($result) {
                return $result;
            }
        }
        http_response_code(401);
        throw new AppException('Unauthorized', 401);
    }

    public function refreshToken(?string $token): array
    {
        if ($this->isTokenValid($token)) {
            $userId = $this->getUserId($token);
            $sql = "
                SELECT login, pass 
                FROM Users 
                WHERE id = :userId
            ";
            $params = [
                [
                    "key"=> ":userId",
                    "value"=> $userId,
                    "type"=> \PDO::PARAM_INT,
                ],
            ];
            $row = $this->db->selectProcess($sql, $params, "fetch");
            if ($row) {
                $validAt = Date("Y-m-d H:i:s", time() + 15 * 60);
                $newToken = md5(time() . $userId . $row['login'] . $row['pass']);
                $sql = "
                    UPDATE Tokens 
                    SET 
                        token = :newToken,
                        validAt = :validAt
                    WHERE token = :token
                ";
                try {
                    $stmt = $this->db->getConn()->prepare($sql);
                    $stmt->bindValue(':newToken', $newToken, \PDO::PARAM_STR);
                    $stmt->bindValue(':validAt', $validAt, \PDO::PARAM_STR);
                    $stmt->bindValue(':token', $token, \PDO::PARAM_STR);
                    $stmt->execute();
                    return ['token' => $newToken, 'validAt' => $validAt];
                }
                catch (\PDOException $e) {
                    Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
                    throw new DatabaseException('Server error', 500);
                }
            }
        }
        http_response_code(401);
        throw new AppException('Unauthorized', 401);
    }

    public function getAuth(?string $token): array
    {
        date_default_timezone_set('Europe/Warsaw');
        if ($this->isTokenValid($token)) {
            $userId = $this->getUserId($token);
            if ($userId !== null) {
                $sql = "SELECT id, groupId, login, isActive, isAdmin
                FROM Users
                WHERE id = :userId";
                $params = [
                    [
                        "key"=> ":userId",
                        "value"=> $userId,
                        "type"=> \PDO::PARAM_INT,
                    ],
                ];
                $row = $this->db->selectProcess($sql, $params, "fetch");
                if ($row) {
                    return $row;          
                }
            }
        }
        http_response_code(401);
        throw new AppException('Unauthorized', 401);
    }

    public function getTokenFromHeader(): ?string
    {
        $headers = apache_request_headers();
        foreach ($headers as $key => $header) {
            if ($key === 'Authorization') {
                return str_replace('Bearer ', '', $header);
            }
        }
        return null;
    }

    /**
     * Check if given token in correct
     * @param string|null $token
     * @return string
     */
    public function checkToken(?string $token): string
    {
        if ($token !== null) {
            if ($this->isTokenValid($token)) {
                return $token;
            }
        }
        http_response_code(401);
        throw new AppException('Unauthorized', 401);
    }

    /**
     * return credentials by given token
     *
     * @param string $token
     * @param array $scopes
     * @return string // 'admin' or 'user'
     */
    public function getAuthorize(string $token, array $scopes): string
    {
        if (
            in_array('admin', $scopes) && $this->isAdminToken($token)
        ) {
            return 'admin';
        }
        if (
            in_array('user', $scopes) && $this->isUserToken($token)
        ) {
            return 'user';
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }

    private function isAdminToken(string $token): bool
    {
        return $this->getAdminValue($token);
    }

    private function isUserToken(string $token): bool
    {
        return !$this->getAdminValue($token);
    }    

    private function getAdminValue(string $token): bool
    {
        $userId = $this->getUserId($token);
        $sql = "SELECT isAdmin FROM Users WHERE id = :userId";
        $params = [
            [
                'key' => ':userId',
                'value' => $userId,
                'type' => \PDO::PARAM_INT,
            ]
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        if ($row) {
            return (bool) $row["isAdmin"];
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }   
    
    private function getAuthData(string $login, string $pass): ?array
    {
        $sql = "
            SELECT id, groupId, login, isActive, isAdmin 
            FROM Users 
            WHERE login = :login AND pass = :pass
        ";
        $params = [
            [
                "key"=> ":login",
                "value"=> $login,
                "type"=> \PDO::PARAM_STR,
            ],
            [
                "key"=> ":pass",
                "value"=> md5($pass),
                "type"=> \PDO::PARAM_STR,                
            ],
        ];
        $row = $this->db->selectProcess($sql, $params, "fetch");
        if ($row) {
            $this->deleteInactiveTokens($row['id']);
            [$token, $validAt] = $this->createToken($row['id'], $login, md5($pass));
            $row['token'] = $token;
            $row['validAt'] = $validAt;
            return $row;          
        }
        return null;
    }

    private function deleteInactiveTokens(int $userId): void
    {
        $valid = Date("Y-m-d H:i:s");
        $sql = "
            DELETE FROM `Tokens`
            WHERE userId = :userId AND validAt < :valid
        ";
        try {
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':valid', $valid, \PDO::PARAM_STR);
            $stmt->execute();
        }
        catch (\PDOException $e) {
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }

    private function createToken(int $userId, string $login, string $pass): array
    {
        $validAt = Date("Y-m-d H:i:s", time() + 15 * 60);
        $token = md5(time() . $userId . $login . $pass);
        $sql = "
            INSERT INTO `Tokens` (userId, token, validAt)
            VALUES (:userId, :token, :validAt)
        ";
        try {
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(':userId', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':token', $token, \PDO::PARAM_STR);
            $stmt->bindValue(':validAt', $validAt, \PDO::PARAM_STR);
            $stmt->execute();
            return [$token, $validAt];
        }
        catch (\PDOException $e) {
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }
}