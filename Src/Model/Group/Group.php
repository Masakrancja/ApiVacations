<?php
declare(strict_types=1);
namespace ApiVacations\Model\Group;

class Group
{
    private int $id;
    private int $userId;
    private string $name;
    private string $address;
    private string $postalCode;
    private string $city;
    private ?string $nip = null;
    private string $createdAt;
    private string $updatedAt;




}