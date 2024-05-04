<?php
declare(strict_types=1);
namespace ApiVacations\Model\User;

use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\AppException;


class UserModel extends AbstractModel
{

    public function get(?int $param1=null, ?int $param2=null): string
    {
        echo 'get<br>';

        if ($param2 === null) {
            if ($param1 === null) {
                $result = $this->getUsers();
            } else {
                $result = $this->getUser($param1);
            }
            return json_encode($result, JSON_UNESCAPED_SLASHES , JSON_UNESCAPED_UNICODE);
        }
        throw new AppException('Bad request', 400);
    }

    public function put(int $param1, ?int $param2=null): string
    {

    }

    public function post(?int $param1=null): string
    {

    }

    public function delete(int $param1): string
    {

    }

    private function getUsers(): array
    {
        echo 'getUsers<br>';
        $sql = "SELECT * FROM Users";
        try {
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $rows ?? [];
        }
        catch (\PDOException $e) {
            throw new DatabaseException('Błąd odczytu danych', 500, $e);
        }        
    }

    public function getUser(int $id): ?array
    {
        echo 'getUser<br>';
        $sql = "SELECT * FROM Users WHERE id = :id";
        try {
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindParam(":id", $id, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?? null;
        }
        catch (\PDOException $e) {
            throw new DatabaseException('Błąd odczytu danych', 500, $e);
        }
    }

    public function addUser(

    ): void
    {

    }


}