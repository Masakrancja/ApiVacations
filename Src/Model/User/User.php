<?php
declare(strict_types=1);
namespace ApiVacations\Model\User;
use ApiVacations\Helpers\Validator;
use ApiVacations\Exceptions\AppException;

class User
{
    private int $id;
    private int $groupId;
    private string $login;
    private string $pass;
    private string $tokenApi;
    private bool $isActive;
    private bool $isAdmin;
    private string $createdAt;
    private string $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $login = Validator::stringSanitize($login);
        if (!Validator::stringValidate($login, 5, 20)) {
            throw new AppException("Value length must be between 5 - 20", 422);
        }
        $this->login = $login;
    }

    public function getPass(): string
    {
        return $this->pass;
    }

    public function setPass(string $pass): void
    {
        $this->pass = $pass;
    }

    public function getTokenApi(): string
    {
        return $this->tokenApi;
    }

    public function setTokenApi(string $tokenApi): void
    {
        $this->tokenApi = $tokenApi;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getIsAdmin(): bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}