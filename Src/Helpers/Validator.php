<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;

class Validator
{
    public static function stringSanitize(string $value): string
    {
        return htmlspecialchars($value);
    }

    public static function stringValidate(string $value, int $minLength=0, int $maxLength=20000000): bool
    {
        if (mb_strlen($value, "UTF8") < $minLength) {
            return false;
        } elseif (mb_strlen($value, "UTF8") > $maxLength) {
            return false;
        }
        return true;
    }

    public static function emailValidate(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
}