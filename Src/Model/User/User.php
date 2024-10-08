<?php
declare(strict_types=1);
namespace ApiVacations\Model\User;

use ApiVacations\Helpers\Validator;
use ApiVacations\Exceptions\AppException;

class User
{
    private ?int $id = null;
    private ?int $groupId = null;
    private string $login;
    private string $pass;
    private string $pass2;
    private bool $isActive = false;
    private bool $isAdmin = false;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): void
    {
        if (!Validator::intValidate($groupId, 1)) {
            throw new AppException('Nie wybrano firmy', 422);
        }
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
            throw new AppException("Login musi zawierać od 5 do 20 znaków", 422);
        }
        $this->login = $login;
    }

    public function getPass(): string
    {
        return $this->pass;
    }

    public function setPass(string $pass): void
    {
        $pass = Validator::stringSanitize($pass);
        if (!Validator::stringValidate($pass, 4, 20)) {
            throw new AppException("Hasło musi zawierać od 4 do 20 znaków", 422);
        }
        $this->pass = $pass;
    }

    public function getPass2(): string
    {
        return $this->pass2;
    }

    public function setPass2(string $pass2): void
    {
        $pass = $this->getPass() ?? "";
        if ($pass !== $pass2) {
            throw new AppException("Podane hasła są niezgodne", 422);
        }
        $this->pass2 = $pass2;
    }

    public function getIsActive(): bool
    {
        return (bool) $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getIsAdmin(): bool
    {
        return (bool) $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}