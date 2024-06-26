<?php
declare(strict_types=1);
namespace ApiVacations\Model\Group;

use ApiVacations\Helpers\Validator;
use ApiVacations\Exceptions\AppException;

class Group
{
    private int $id;
    private int $userId;
    private string $name;
    private string $address;
    private string $postalCode;
    private string $city;
    private string $nip;
    private string $createdAt;
    private string $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getNip(): string
    {
        return $this->nip;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function setName(string $name): void
    {
        $name = Validator::stringSanitize($name);
        if (!Validator::stringValidate($name, 3, 20)) {
            throw new AppException("Nazwa firmy musi być w przedziale od 3 do 20 znaków", 422);
        }
        $this->name = $name;
    }

    public function setAddress(string $address): void
    {
        $address = Validator::stringSanitize($address);
        if (!Validator::stringValidate($address, 5, 20)) {
            throw new AppException("Adres musi być w przedziale od 5 do 20 znaków", 422);
        }
        $this->address = $address;
    }

    public function setPostalCode(string $postalCode): void
    {
        $postalCode = Validator::stringSanitize($postalCode);
        if (!Validator::stringValidate($postalCode, 0, 10)) {
            throw new AppException("Kod pocztowy musi mieć nie więcej niż 10 znaków", 422);
        }
        $this->postalCode = $postalCode;
    }

    public function setCity(string $city): void
    {
        $city = Validator::stringSanitize($city);
        if (!Validator::stringValidate($city, 2, 20)) {
            throw new AppException("Nazwa miasta musi mieć od 2 do 20 znaków", 422);
        }
        $this->city = $city;
    }

    public function setNip(string $nip): void
    {
        $nip = preg_replace('~\D~', '', Validator::stringSanitize($nip));
        if (!Validator::stringValidate($nip, 10, 11)) {
            throw new AppException("Nip firmy może mieć 10 lub 11 cyfr", 422);
        }
        $this->nip = $nip;
    }

    public function setCreatedAt(string $createdAt): void
    {

        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(string $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }



}