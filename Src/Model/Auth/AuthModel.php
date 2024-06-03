<?php
declare(strict_types=1);
namespace ApiVacations\Model\Auth;

use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\AppException;

class AuthModel extends AbstractModel
{
    public function getToken(): ?string
    {
        $headers = apache_request_headers();
        foreach ($headers as $key => $header) {
            if ($key === 'X-API-KEY') {
                return $header;
            }
        }
        return null;
    }

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
}