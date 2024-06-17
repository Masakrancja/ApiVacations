<?php
declare(strict_types=1);
namespace ApiVacations\Model\Auth;

use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\AppException;

class AuthModel extends AbstractModel
{
    /**
     * Get authorization yserdata
     *
     * @param object|null $data
     * @return array
     */
    public function getAuth(?object $data): array
    {
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

    public function getMe(?string $token): ?array
    {
        if ($token) {
            return $this->getAuthDataByToken($token);
        }
        return null;
    }

    /**
     * Get token from header
     *
     * @return string|null
     */
    public function getTokenFromParams($params): ?string
    {
        return $params['token'] ?? null;
    }

    /**
     * Check if given token in correct
     *
     * @param string|null $token
     * @return string
     */
    public function checkToken(?string $token): string
    {
        if ($token !== null) {
            $sql = "SELECT id FROM Users WHERE tokenApi = :token";
            $params = [
                [
                    'key' => ':token',
                    'value' => $token,
                    'type' => \PDO::PARAM_STR,
                ]
            ];
            $row = $this->db->selectProcess($sql, $params, 'fetch');
            if ($row) {
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
        $sql = "SELECT isAdmin FROM Users WHERE tokenApi = :token";
        $params = [
            [
                'key' => ':token',
                'value' => $token,
                'type' => \PDO::PARAM_STR,
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
            SELECT id, groupId, login, tokenApi, isActive, isAdmin 
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
            return $row;          
        }
        return null;
    }

    private function getAuthDataByToken(string $token): ?array
    {
        $sql = "SELECT id, groupId, login, isActive, isAdmin
                FROM Users
                WHERE tokenApi = :token";
        $params = [
            [
                "key"=> ":token",
                "value"=> $token,
                "type"=> \PDO::PARAM_STR,
            ],
        ];
        $row = $this->db->selectProcess($sql, $params, "fetch");
        if ($row) {
            return $row;          
        }
        return null;
    }
}