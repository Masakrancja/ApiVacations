<?php
declare(strict_types=1);
namespace ApiVacations\Model\User;
use ApiVacations\Helpers\Validator;
use ApiVacations\Exceptions\AppException;

class UserData
{
    private $id;
    private $userId;
    private $firstName;
    private $lastName;
    private $address;
    private $postalCode;
    private $city;
    private $phone;
    private $email;
    private $createdAt;
    private $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $firstName = Validator::stringSanitize($firstName);
        if (!Validator::stringValidate($firstName, 2, 30)) {
            throw new AppException("Value length must be between 2 - 30", 422);
        }        
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $lastName = Validator::stringSanitize($lastName);
        if (!Validator::stringValidate($lastName, 2, 30)) {
            throw new AppException("Value length must be between 2 - 30", 422);
        }  
        $this->lastName = $lastName;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $address = Validator::stringSanitize($address);
        if (!Validator::stringValidate($address, 5)) {
            throw new AppException("Value length must great or equal 5", 422);
        }  
        $this->address = $address;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): void
    {
        $postalCode = Validator::stringSanitize($postalCode);
        if (!Validator::stringValidate($postalCode, 0, 10)) {
            throw new AppException("Value length must less or equal 10", 422);
        }  
        $this->postalCode = $postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $city = Validator::stringSanitize($city);
        if (!Validator::stringValidate($city, 2, 30)) {
            throw new AppException("Value length must be between 2 - 30", 422);
        }  
        $this->city = $city;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $phone = Validator::stringSanitize($phone);
        if (!Validator::stringValidate($phone, 6, 20)) {
            throw new AppException("Value length must be between 6 - 20", 422);
        }          
        $this->phone = $phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $email = Validator::stringSanitize($email);
        if (!Validator::stringValidate($email, 5)) {
            throw new AppException("Value length must great or equal 5", 422);
        }
        if (!Validator::emailValidate($email)) {
            throw new AppException("Error email format", 422);
        }
        $this->email = $email;
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

