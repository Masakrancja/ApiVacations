<?php
declare(strict_types=1);
namespace ApiVacations\Model\User\User;

class User
{
    private int $id;
    private int $group_id;
    private string $login;
    private string $pass;
    private string $token_api;
    private bool $isActive;
    private bool $isAdmin;
    private string $createdAt;
    private string $updatedAt;

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
        return $this->group_id;
    }

    public function setGroupId(int $group_id): void
    {
        $this->group_id = $group_id;
    }



}