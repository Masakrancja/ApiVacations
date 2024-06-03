<?php
declare(strict_types=1);
namespace ApiVacations\Model\Event;

use ApiVacations\Model\AbstractModel;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Exceptions\AppException;
use ApiVacations\Helpers\Logger;

class EventModel extends AbstractModel
{
    /**
     * Get all events from Database. Default first 10 events
     *
     * @param array|null $params // keys: int limit, int offset
     * @param string $token // X-API-KEY token
     * @param string $authorize // 'admin' or 'user'
     * @return array
     */
    public function getEvents(?array $params, string $token, string $authorize): array
    {
        $offset = (int) ($params['offset'] ?? 0);
        $offset = ($limit < 0) ? 0 : $offset;
        $limit = (int) ($params['limit'] ?? 10);
        $limit =  ($limit > 25) ? 10 : $limit;

        $userId = ($params['userid'] ?? null) ? (int) $params['userid'] : null;
        if ($userId) {
            if (!$this->isItMyUser($token, (int) $userId)) {
                http_response_code(404);
                throw new AppException('Not found', 404);
            }
            $userId = (int) $userId;
        }
        if ($authorize === 'admin') {
            $groupId = $this->getUserGroupId($token);
            return $this->getEventsFromDBAsAdmin(
                $offset, $limit, $groupId, $userId
            );
        }
        if ($authorize === 'user') {
            $userId = $this->getUserId($token);
            return $this->getEventsFromDBAsUser(
                $offset, $limit, $userId
            );
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }

        /**
     * Get particular user
     *
     * @param integer $id // User ID
     * @param string $token // X-API-KEY token
     * @param string $authorize // 'admin' or 'user'
     * @return array|null
     */
    public function getEvent(int $id, string $token, string $authorize): ?array
    {
        $userId = $this->getEventUserId($id);
        if ($authorize === 'admin') {
            if ($this->isItMyUser($token, $userId)) {
                return $this->getEventFromDB($id, false);
            } 
        }
        if ($authorize === 'user') {
            if ($this->isItMe($token, $userId)) {
                return $this->getEventFromDB($id, true);
            }
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }

    public function addEvent(?object $data, string $token, string $authorize): ?array
    {
        echo json_encode($data) . "\n";
     
        if ($authorize !== 'user' OR $this->isAdmin($token)) {
            http_response_code(403);
            throw new AppException('Forbidden', 403);            
        }
        if ($data === null) {
            http_response_code(422);
            throw new AppException('Empty data', 422);
        } 

        $this->event->setUserId(
            $this->getUserId($token)
        );
        $this->event->setGroupId(
            $this->getUserGroupId($token)
        );
        $this->event->setReasonId(
            $this->checkReason((int) ($data->reasonId ?? null))
        );
        $this->event->setDateFrom(
            (string) ($data->dateFrom ?? null)
        );
        $this->event->setDateTo(
            (string) ($data->dateTo ?? null)
        );
        if ($this->event->getDateFrom() > $this->event->getDateTo()) {
            throw new AppException('dateFrom must be less or equal than dateTo', 422);
        }
        $this->event->setDays(
            (int) floor(
            (strtotime($this->event->getDateTo()) - strtotime($this->event->getDateFrom())) 
            / 86400) + 1
        );
        $this->event->setIsApproved(false);
        $this->event->setNotice(
            (string) ($data->notice ?? null)
        );
        $eventId = $this->addEventToDB();
        return $this->getEventFromDB($eventId, true);
    }







    private function getEventsFromDBAsAdmin(
        int $offset, int $limit, int $groupId, ?int $userId
    ): array
    {
        $result = [];
        $params = [];
        $sql = "
            SELECT id, userId, groupId, reasonId, dateFrom, dateTo, days, isApproved, createdAt, updatedAt   
            FROM Events 
            WHERE groupId = :groupId 
        ";
        $params[] = [
            'key' => ':groupId',
            'value' => $groupId,
            'type' => \PDO::PARAM_INT,
        ];
        if ($userId) {
            $sql .= " AND userId = :userId ";
            $params[] = [
                'key' => ':userId',
                'value' => $userId,
                'type' => \PDO::PARAM_INT,
            ];
        }
        $sql .= " ORDER BY createdAt DESC";
        $sql .= " LIMIT " . $offset . ", " . $limit;
        $rows = $this->db->selectProcess($sql, $params, 'fetchAll');
        foreach($rows as $row) {
            $result[] = $row;            
        }
        return $result;
    }

    private function getEventsFromDBAsUser(
        int $offset, int $limit, int $userId
    ): array
    {
        $result = [];
        $params = [];
        $sql = "
            SELECT id, userId, groupId, reasonId, dateFrom, dateTo, days, isApproved, notice, createdAt, updatedAt   
            FROM Events 
            WHERE userId = :userId 
        ";
        $params[] = [
            'key' => ':userId',
            'value' => $userId,
            'type' => \PDO::PARAM_INT,
        ];
        $sql .= " ORDER BY createdAt DESC";
        $sql .= " LIMIT " . $offset . ", " . $limit;
        $rows = $this->db->selectProcess($sql, $params, 'fetchAll');
        foreach($rows as $row) {
            $result[] = $row;            
        }
        return $result;
    }

    private function getEventUserId(int $id): int
    {
        $sql = "
            SELECT userId
            FROM Events
            WHERE id = :id
        ";
        $params = [
            [
                "key"=> ":id",
                "value"=> $id,
                "type"=> \PDO::PARAM_INT,
            ],
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        if ($row) {
            return (int) $row['userId'];
        }
        http_response_code(404);
        throw new AppException('Not found', 404);
    }

    private function getEventFromDB(int $id, bool $notice=false): ?array
    {
        if ($notice) {
            $sql = "
            SELECT id, userId, groupId, reasonId, dateFrom, dateTo, days, isApproved, notice, createdAt, updatedAt  
        ";
        } else {
            $sql = "
            SELECT id, userId, groupId, reasonId, dateFrom, dateTo, days, isApproved, createdAt, updatedAt  
        ";
        }

        $sql .= "
            FROM Events 
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

    private function addEventToDB(): int
    {
        $sql = "
            INSERT INTO Events (userId, groupId, reasonId, dateFrom, dateTo, days, isApproved, notice) 
            Values (:userId, :groupId, :reasonId, :dateFrom, :dateTo, :days, :isApproved, :notice)
        ";
        try {
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(":userId", $this->event->getUserId(), \PDO::PARAM_INT);
            $stmt->bindValue(":groupId", $this->event->getGroupId(), \PDO::PARAM_INT);
            $stmt->bindValue(":reasonId", $this->event->getReasonId(), \PDO::PARAM_INT);
            $stmt->bindValue(":dateFrom", $this->event->getDateFrom(), \PDO::PARAM_STR);
            $stmt->bindValue(":dateTo", $this->event->getDateTo(), \PDO::PARAM_STR);
            $stmt->bindValue(":days", $this->event->getDays(), \PDO::PARAM_INT);
            $stmt->bindValue(":isApproved", $this->event->getIsApproved(), \PDO::PARAM_BOOL);
            $stmt->bindValue(":notice", $this->event->getNotice(), \PDO::PARAM_STR);
            $stmt->execute();
            return (int) $this->db->getConn()->lastInsertId();
        }
        catch (\PDOException $e) {
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }

}