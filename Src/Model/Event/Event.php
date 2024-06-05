<?php
declare(strict_types=1);
namespace ApiVacations\Model\Event;

use ApiVacations\Helpers\Validator;
use ApiVacations\Exceptions\AppException;

class Event
{
    private ?int $id;
    private int $userId;
    private int $groupId;
    private int $reasonId;
    private string $dateFrom;
    private string $dateTo;
    private int $days;
    private string $status = 'pending';
    private string $notice;
    private ?string $createdAt;
    private string $updatedAt;

    public function getId()
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getReasonId(): int
    {
        return $this->reasonId;
    }

    public function getDateFrom(): string
    {
        return $this->dateFrom;
    }

    public function getDateTo(): string
    {
        return $this->dateTo;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getNotice(): string
    {
        return $this->notice;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function setGroupId(int $groupId)
    {
        $this->groupId = $groupId;
    }

    public function setReasonId(int $reasonId)
    {
        $this->reasonId = $reasonId;
    }

    public function setDateFrom(string $dateFrom)
    {
        $dateFrom = Validator::stringSanitize($dateFrom);
        if (!Validator::dateTypeValidate($dateFrom)) {
            throw new AppException("Date format must be: YYYY-MM-DD", 422);
        }
        if (!Validator::dateValueValidate($dateFrom)) {
            throw new AppException("Incorrect date value", 422);
        }
        $from = Date("Y-m-d", time() - 90 * 86400);
        $to = Date("Y-m-d", time() + 90 * 86400);
        if (!Validator::dateRangeValidate($dateFrom, $from, $to)) {
            throw new AppException("Date value must be in range: " . $from . " - " . $to , 422);
        }
        $this->dateFrom = $dateFrom;
    }

    public function setDateTo(string $dateTo)
    {
        $dateTo = Validator::stringSanitize($dateTo);
        if (!Validator::dateTypeValidate($dateTo)) {
            throw new AppException("Date format must be: YYYY-MM-DD", 422);
        }
        if (!Validator::dateValueValidate($dateTo)) {
            throw new AppException("Incorrect date value", 422);
        }
        $from = Date("Y-m-d", time() - 90 * 86400);
        $to = Date("Y-m-d", time() + 90 * 86400);
        if (!Validator::dateRangeValidate($dateTo, $from, $to)) {
            throw new AppException("Date value must be in range: " . $from . " - " . $to , 422);
        }
        $this->dateTo = $dateTo;
    }

    public function setDays(int $days)
    {
        $this->days = $days;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function setNotice(string $notice)
    {
        $this->notice = Validator::stringSanitize($notice);
    }

    public function setCreatedAt(string $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(string $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}