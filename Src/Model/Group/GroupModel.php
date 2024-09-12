<?php
declare(strict_types=1);
namespace ApiVacations\Model\Group;

use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Helpers\Logger;

class GroupModel extends AbstractModel
{
    /**
     * Get all groups from Database.
     *
     * @param array|null $params // keys: int limit, int offset
     * @param string $token
     * @param string $authorize // 'admin' or 'user'
     * @return array
     */
    public function getGroups(): array
    {
        return $this->getGroupsFromDB();
    }

    /**
     * Get particular group
     *
     * @param integer $id // group ID
     * @param string $token 
     * @param string $authorize // 'admin' or 'user'
     * @return array|null
     */
    public function getGroup(int $id): ?array
    {
        return $this->getGroupFromDB($id);
    }

    /**
     * Edit particular group
     *
     * @param object|null $data
     * @param string $token
     * @param string $authorize
     * @param integer $id
     * @return array|null
     */
    public function editGroup(
        ?object $data,
        string $token,
        string $authorize,
        int $id
    ): ?array {
        $groupId = $this->getUserGroupId($token);
        $group = $this->getGroup($id);
        if (!$group) {
            http_response_code(404);
            throw new AppException('Not found', 404);
        }
        if ($groupId <> $id or !$this->isAdmin($token)) {
            http_response_code(403);
            throw new AppException('Forbidden', 403);
        }
        if (!$this->isUserActive($token)) {
            http_response_code(403);
            throw new AppException('Forbidden', 403);
        }
        $this->group->setName((string) ($data->name ?? $group['name']));
        $this->group->setAddress((string) ($data->address ?? $group['address']));
        $this->group->setPostalCode((string) ($data->postalCode ?? $group['postalCode']));
        $this->group->setCity((string) ($data->city ?? $group['city']));
        $rowCount = $this->editGroupInDB($id);
        return $this->getGroupFromDB($id);
    }

    private function getGroupsFromDB(): array
    {
        $result = [];
        $sql = "
            SELECT id, name, city, nip 
            FROM `Groups`  
            ORDER BY name ASC 
        ";
        $rows = $this->db->selectProcess($sql, [], 'fetchAll');
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    private function getGroupFromDB(int $id): ?array
    {
        $sql = "
            SELECT id, userId, name, address, postalCode, city, nip, createdAt, updatedAt
            FROM `Groups` 
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
            return $row;
        }
        return null;
    }

    private function editGroupInDB(int $id): int
    {
        try {
            $sql = "
                UPDATE `Groups` 
                SET 
                    name = :name, 
                    address = :address, 
                    postalCode = :postalCode, 
                    city = :city
                WHERE id = :id
            ";
            $params = [
                [
                    'key' => ':name',
                    'value' => $this->group->getName(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':address',
                    'value' => $this->group->getAddress(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':postalCode',
                    'value' => $this->group->getPostalCode(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':city',
                    'value' => $this->group->getCity(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':id',
                    'value' => $id,
                    'type' => \PDO::PARAM_INT,
                ],
            ];

            $stmt = $this->db->getConn()->prepare($sql);
            foreach ($params as $param) {
                $stmt->bindValue($param['key'], $param['value'], $param['type']);
            }
            $stmt->execute();

            return (int) $stmt->rowCount();
        } catch (\PDOException $e) {
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }
}