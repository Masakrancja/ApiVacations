<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Helpers\Logger;

class Auth
{
    private DB $db;
    public function __construct(DB $db)
    {
        $this->db = $db;
    }

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

    public function isAdminToken(?string $token): bool
    {
        return $this->getAdminValue($token);
    }

    public function isUserToken(?string $token): bool
    {
        return !$this->getAdminValue($token);
    }    

    private function getAdminValue(?string $token): bool
    {
        if ($token !== null) {
            $sql = "SELECT isAdmin FROM Users WHERE token_api = :token";
            try {
                $stmt = $this->db->getConn()->prepare($sql);
                $stmt->bindParam(":token", $token, \PDO::PARAM_STR);
                $stmt->execute();
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    return (bool) $row["isAdmin"];
                }
            }
            catch (\PDOException $e) {
                Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
                throw new DatabaseException('Server error', 500);
            }
        }
        throw new AppException('Unathorized', 401);
    }
}