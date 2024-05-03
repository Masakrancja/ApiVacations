<?php
declare(strict_types=1);
namespace ApiVacations\Model\User;

use ApiVacations\Model\User\UserData;

class User 
{
    private int $id;
    private int $group_id;
    private string $pass;
    private string $token_api;
    private bool $isActive;
    private bool $isAdmin;
    private string $createdAt;
    private string $updatedAt;
    private UserData $userData;
    private Group $group;

    public function __construct()
    {

    }

    public function getUsers(): array
    {

    }

    public function getUser(int $id): array
    {

    }

    public function addUser(

    ): void
    {

    }


}