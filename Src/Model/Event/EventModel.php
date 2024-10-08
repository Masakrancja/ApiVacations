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
     * Possible statuses in Events
     *
     * @var array
     */
    private array $possibleEventStatuses = ['approved', 'pending', 'cancelled'];

    /**
     * Get all events from Database. Default first 10 events
     *
     * @param array|null $params // keys: int limit, int offset
     * @param string $token // token
     * @param string $authorize // 'admin' or 'user'
     * @return array
     */
    public function getEvents(?array $params, string $token, string $authorize): array
    {
        $offset = (int) ($params['offset'] ?? 0);
        $limit = (int) ($params['limit'] ?? 1000);
        // $offset = ($offset < 0) ? 0 : $offset;
        // $limit = ($limit > 25) ? 10 : $limit;
        $userId = (isset($params['userid'])) ? (int) $params['userid'] : null;
        if ($userId !== null) {
            if (!$this->isItMyUser($token, (int) $userId)) {
                http_response_code(404);
                throw new AppException('Not found', 404);
            }
            $userId = (int) $userId;
        }

        if ($authorize === 'admin') {
            $groupId = $this->getUserGroupId($token);
            return [
                $this->getEventsFromDBAsAdmin(
                    $offset,
                    $limit,
                    $groupId,
                    $userId
                ),
                $this->getCountAllEventsFromDBAsAdmin(
                    $groupId,
                    $userId
                )
            ];
        }
        if ($authorize === 'user') {
            $userId = $this->getUserId($token);
            return [
                $this->getEventsFromDBAsUser(
                    $offset,
                    $limit,
                    $userId
                ),
                $this->getCountAllEventsFromDBAsUser(
                    $userId
                )
            ];
        }
        http_response_code(403);
        throw new AppException('Forbidden', 403);
    }

    /**
     * Get particular event
     *
     * @param integer $id
     * @param string $token
     * @param string $authorize
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

    /**
     * Add new event
     *
     * @param object|null $data
     * @param string $token
     * @param string $authorize
     * @return array|null
     */
    public function addEvent(?object $data, string $token, string $authorize): ?array
    {
        if ($authorize !== 'user' or $this->isAdmin($token)) {
            http_response_code(403);
            throw new AppException('Forbidden. Tylko pracownik może tworzyć urlopy', 403);
        }
        if ($data === null) {
            http_response_code(422);
            throw new AppException('Empty data', 422);
        }

        if (!$this->isUserActive($token)) {
            http_response_code(403);
            throw new AppException('Forbidden. Pracownik nieaktywny', 403);
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
            throw new AppException('Data początkowa musi być mniejsza lub równa od daty końcowej', 422);
        }
        $this->event->setDays(
            $this->calculateDays(
                $this->event->getDateFrom(),
                $this->event->getDateTo()
            )
        );

        $this->event->setStatus('pending');
        $this->event->setNotice(
            (string) ($data->notice ?? null)
        );
        $result = $this->checkUsedDates(
            $this->getUserId($token),
            $this->event->getDateFrom(),
            $this->event->getDateTo()
        );
        if (!empty($result)) {
            sort($result);
            $c = count($result);
            if ($c > 1) {
                $msg = 'Dni: ' . implode(', ', $result) . ' zostały już wybrane jako urlopowe.';
            } else {
                $msg = 'Dzień ' . $result[0] . ' został już wybrany jako urlopowy.';
            }
            $msg .= ' Wybierz inny zakres dat.';
            throw new AppException($msg, 422);
        }
        $eventId = $this->addEventToDB();
        return $this->getEventFromDB($eventId, true);
    }

    /**
     * Edit given event
     *
     * @param object|null $data
     * @param string $token
     * @param string $authorize
     * @param integer $id
     * @return array|null
     */
    public function editEvent(
        ?object $data,
        string $token,
        string $authorize,
        int $id
    ): ?array {
        $event = $this->getEvent($id, $token, $authorize);

        if (!$event) {
            http_response_code(404);
            throw new AppException('Not found', 404);
        }
        if (!$this->isUserActive($token)) {
            http_response_code(403);
            throw new AppException('Forbidden', 403);
        }
        if ($this->isAdmin($token)) {
            if (
                isset($data->status) and
                !in_array($data->status, $this->possibleEventStatuses)
            ) {
                http_response_code(422);
                throw new AppException(
                    'Incorrect status. Possible only: ' .
                    implode(', ', $this->possibleEventStatuses),
                    422
                );
            }
            $this->event->setStatus((string) ($data->status ?? $event['status']));
            $rowCount = $this->editEventInDBforAdmin($id);
            return $this->getEventFromDB($id, false);
        } else {
            if (isset($data->wantCancel)) {
                if ($event['status'] === 'approved') {
                    if ($event['dateFrom'] > Date("Y-m-d")) {
                        if (in_array($data->wantCancel, ["no", "yes"])) {
                            $this->event->setWantCancel($data->wantCancel);
                            $rowCount = $this->setEventWantCancel($id);
                        }
                        return $this->getEventFromDB($id, true);
                    } else {
                        throw new AppException('Prośbę o anulowanie urlopu można tylko wysyłać do nierozpoczętych jeszcze urlopów.', 422);
                    }
                } else {
                    throw new AppException('Prośbę o anulowanie urlopu można tylko wysyłać do urlopów zaakceptowanych.', 422);
                }
            } else {
                if ($event['status'] === 'approved') {
                    throw new AppException('Nie można edytować zaakceptowanego urlopu.', 422);
                } elseif ($event['status'] === 'cancelled') {
                    throw new AppException('Nie można edytować anulowanego urlopu.', 422);
                } else {
                    $this->event->setReasonId(
                        $this->checkReason(
                            (int) ($data->reasonId ?? $event['reasonId'])
                        )
                    );

                    $this->event->setDateFrom($data->dateFrom ?? $event['dateFrom']);
                    $this->event->setDateTo($data->dateTo ?? $event['dateTo']);

                    if ($this->event->getDateFrom() > $this->event->getDateTo()) {
                        throw new AppException('Data początkowa musi być mniejsza lub równa od daty końcowej', 422);
                    }

                    $this->event->setDays(
                        $this->calculateDays(
                            $this->event->getDateFrom(),
                            $this->event->getDateTo()
                        )
                    );
                    $this->event->setNotice($data->notice ?? $event['notice']);

                    $result = $this->checkUsedDates(
                        $this->getUserId($token),
                        $this->event->getDateFrom(),
                        $this->event->getDateTo(),
                        $id
                    );

                    if (!empty($result)) {
                        sort($result);
                        $c = count($result);
                        if ($c > 1) {
                            $msg = 'Dni: ' . implode(', ', $result) . ' zostały już wybrane jako urlopowe.';
                        } else {
                            $msg = 'Dzień ' . $result[0] . ' został już wybrany jako urlopowy.';
                        }
                        $msg .= ' Wybierz inny zakres dat.';
                        throw new AppException($msg, 422);
                    }

                    $rowCount = $this->editEventInDBforUser($id);
                    return $this->getEventFromDB($id, true);
                }
            }
        }
    }

    /**
     * Delete given event
     *
     * @param string $token
     * @param string $authorize
     * @param integer $id
     * @return void
     */
    public function deleteEvent(
        string $token,
        string $authorize,
        int $id
    ): void {
        if ($authorize !== 'user' or $this->isAdmin($token)) {
            http_response_code(403);
            throw new AppException('Forbidden', 403);
        }
        if (!$this->getEvent($id, $token, $authorize)) {
            http_response_code(404);
            throw new AppException('Not found', 404);
        }
        if (!$this->isUserActive($token)) {
            http_response_code(403);
            throw new AppException('Forbidden', 403);
        }
        $this->deleteEventFromDB($id);
    }

    private function getEventsFromDBAsAdmin(
        int $offset,
        int $limit,
        int $groupId,
        ?int $userId
    ): array {
        $result = [];
        $params = [];
        $sql = "
            SELECT id, userId, groupId, reasonId, dateFrom, dateTo, days, status, wantCancel, createdAt, updatedAt   
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
        foreach ($rows as $row) {
            $row['reasonName'] = $this->getReasonName((int) $row['reasonId']);
            $result[] = $row;
        }
        return $result;
    }

    private function getCountAllEventsFromDBAsAdmin(
        int $groupId,
        ?int $userId
    ): int {
        $sql = "
            SELECT COUNT(*) AS count   
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
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        return (int) ($row['count'] ?? 0);
    }

    private function getEventsFromDBAsUser(
        int $offset,
        int $limit,
        int $userId
    ): array {
        $result = [];
        $params = [];
        $sql = "
            SELECT id, userId, groupId, reasonId, dateFrom, dateTo, days, status, notice, wantCancel, createdAt, updatedAt   
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
        foreach ($rows as $row) {
            $row['reasonName'] = $this->getReasonName((int) $row['reasonId']);
            $result[] = $row;
        }
        return $result;
    }

    private function getCountAllEventsFromDBAsUser(
        int $userId
    ): int {
        $sql = "
            SELECT COUNT(*) AS count   
            FROM Events 
            WHERE userId = :userId 
        ";
        $params[] = [
            'key' => ':userId',
            'value' => $userId,
            'type' => \PDO::PARAM_INT,
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        return (int) ($row['count'] ?? 0);
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
                "key" => ":id",
                "value" => $id,
                "type" => \PDO::PARAM_INT,
            ],
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        if ($row) {
            return (int) $row['userId'];
        }
        http_response_code(404);
        throw new AppException('Not found', 404);
    }

    private function getEventFromDB(
        int $id,
        bool $notice = false
    ): ?array {
        if ($notice) {
            $sql = "
            SELECT id, userId, groupId, reasonId, dateFrom, dateTo, days, status, notice, wantCancel, createdAt, updatedAt  
        ";
        } else {
            $sql = "
            SELECT id, userId, groupId, reasonId, dateFrom, dateTo, days, status, wantCancel, createdAt, updatedAt  
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
            $row['reasonName'] = $this->getReasonName((int) $row['reasonId']);
            return $row;
        }
        return null;
    }

    private function getReasonName(int $reasonId): string
    {
        $sql = "
            SELECT name 
            FROM Reasons 
            WHERE id = :reasonId
        ";
        $params = [
            [
                "key" => ":reasonId",
                "value" => $reasonId,
                "type" => \PDO::PARAM_INT,
            ],
        ];
        $row = $this->db->selectProcess($sql, $params, 'fetch');
        return ($row['name'] ?? '');
    }

    private function addEventToDB(): int
    {
        $sql = "
            INSERT INTO Events (userId, groupId, reasonId, dateFrom, dateTo, days, status, notice) 
            Values (:userId, :groupId, :reasonId, :dateFrom, :dateTo, :days, :status, :notice)
        ";
        try {
            $stmt = $this->db->getConn()->prepare($sql);
            $stmt->bindValue(":userId", $this->event->getUserId(), \PDO::PARAM_INT);
            $stmt->bindValue(":groupId", $this->event->getGroupId(), \PDO::PARAM_INT);
            $stmt->bindValue(":reasonId", $this->event->getReasonId(), \PDO::PARAM_INT);
            $stmt->bindValue(":dateFrom", $this->event->getDateFrom(), \PDO::PARAM_STR);
            $stmt->bindValue(":dateTo", $this->event->getDateTo(), \PDO::PARAM_STR);
            $stmt->bindValue(":days", $this->event->getDays(), \PDO::PARAM_INT);
            $stmt->bindValue(":status", $this->event->getStatus(), \PDO::PARAM_STR);
            $stmt->bindValue(":notice", $this->event->getNotice(), \PDO::PARAM_STR);
            $stmt->execute();
            return (int) $this->db->getConn()->lastInsertId();
        } catch (\PDOException $e) {
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }

    private function editEventInDBforAdmin(int $id): int
    {
        try {
            $sql = "
                UPDATE `Events` 
                SET 
                    status = :status,
                    wantCancel = 'no'
                WHERE id = :id
            ";
            $params = [
                [
                    'key' => ':status',
                    'value' => $this->event->getStatus(),
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

    private function editEventInDBforUser(int $id): int
    {
        try {
            $sql = "
                UPDATE `Events` 
                SET 
                    reasonId = :reasonId,
                    dateFrom = :dateFrom,
                    dateTo = :dateTo,
                    days = :days,
                    notice = :notice
                WHERE id = :id
            ";
            $params = [
                [
                    'key' => ':reasonId',
                    'value' => $this->event->getReasonId(),
                    'type' => \PDO::PARAM_INT,
                ],
                [
                    'key' => ':dateFrom',
                    'value' => $this->event->getDateFrom(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':dateTo',
                    'value' => $this->event->getDateTo(),
                    'type' => \PDO::PARAM_STR,
                ],
                [
                    'key' => ':days',
                    'value' => $this->event->getDays(),
                    'type' => \PDO::PARAM_INT,
                ],
                [
                    'key' => ':notice',
                    'value' => $this->event->getNotice(),
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

    private function setEventWantCancel($id): int
    {
        try {
            $sql = "
                UPDATE `Events` 
                SET 
                    wantCancel = :wantCancel
                WHERE id = :id
            ";
            $params = [
                [
                    'key' => ':wantCancel',
                    'value' => $this->event->getWantCancel(),
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

    private function deleteEventFromDB(int $id): void
    {
        $params = [
            [
                'key' => ':id',
                'value' => $id,
                'type' => \PDO::PARAM_INT,
            ],
        ];
        try {
            $sql = "DELETE FROM Events WHERE id = :id";
            $stmt = $this->db->getConn()->prepare($sql);
            foreach ($params as $param) {
                $stmt->bindValue($param['key'], $param['value'], $param['type']);
            }
            $stmt->execute();
        } catch (\PDOException $e) {
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }

    private function calculateDays(
        string $dateFrom,
        string $dateTo
    ): int {
        return (int) (
            floor(
                (strtotime($dateTo) - strtotime($dateFrom)) / 86400
            )
            + 1
        );
    }

    private function checkUsedDates(
        int $userId,
        string $dateFrom,
        string $dateTo,
        ?int $eventId = null
    ): array {
        $result = [];
        $allDays = $this->getAllDays($dateFrom, $dateTo);

        $sql = "
            SELECT id, dateFrom, dateTo 
            FROM Events 
            WHERE userId = :userId && status != 'cancelled'
            ";
        $params = [
            [
                'key' => ':userId',
                'value' => $userId,
                'type' => \PDO::PARAM_INT,
            ]
        ];
        $rows = $this->db->selectProcess($sql, $params, 'fetchAll');
        foreach ($rows as $row) {
            if ($eventId !== null) {
                if ($row['id'] === $eventId) {
                    continue;
                }
            }
            $commonDays = array_intersect(
                $allDays,
                $this->getAllDays($row['dateFrom'], $row['dateTo'])
            );
            foreach ($commonDays as $commonDay) {
                if (!in_array($commonDay, $result)) {
                    $result[] = $commonDay;
                }
            }
        }
        return $result;
    }

    private function getAllDays(string $dateFrom, string $dateTo): array
    {
        $result = [];
        if ($dateFrom > $dateTo) {
            return $result;
        }
        $date = $dateFrom;
        while ($date <= $dateTo) {
            $result[] = $date;
            $date = Date("Y-m-d", strtotime($date . ' + 1 day'));
        }
        return $result;
    }


}