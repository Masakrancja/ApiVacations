<?php
declare(strict_types=1);
namespace ApiVacations\Model\Group;

class GroupModel
{
    private string $name;
    private string $address;
    private string $postalCode;
    private string $city;
    private ?string $nip = null;
    private ?int $user_id = null;

    public function __construct(
        string $name, string $address, string $postalCode, string $city, ?string $nip, ?int $user_id = null
    )
    {
        $this->name = $name;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->city = $city;
        $this->nip = $nip;
        $this->user_id = $user_id;
    }

    
}