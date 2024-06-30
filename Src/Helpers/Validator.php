<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;

class Validator
{
    /**
     * Escape all entities
     *
     * @param string $value
     * @return string
     */
    public static function stringSanitize(string $value): string
    {
        return htmlspecialchars($value);
    }

    /**
     * Validate string. Check min and max length
     *
     * @param string $value
     * @param integer $minLength
     * @param integer $maxLength
     * @return boolean
     */
    public static function stringValidate(string $value, int $minLength=0, int $maxLength=20000000): bool
    {
        if (mb_strlen($value, "UTF8") < $minLength) {
            return false;
        } elseif (mb_strlen($value, "UTF8") > $maxLength) {
            return false;
        }
        return true;
    }

    /**
     * Validate integer. Check mix and max value
     *
     * @param integer $value
     * @param integer $min
     * @param int $max
     * @return boolean
     */
    public static function intValidate(
        int $value, int $min=0, int $max=PHP_INT_MAX
    ): bool 
    {
        if ($value >= $min && $value <= $max) {
            return true;
        }
        return false;
    }

    /**
     * Validate email.
     *
     * @param string $email
     * @return boolean
     */
    public static function emailValidate(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function dateTypeValidate(string $value): bool
    {
        return (bool) preg_match('~^\d{4}-\d{2}-\d{2}$~', $value);
    }

    public static function dateValueValidate(string $value): bool
    {
        if (preg_match('~^(\d{4})-(\d{2})-(\d{2})$~', $value, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];
            return checkdate($month, $day, $year);
        }
        return false;
    }

    public static function dateRangeValidate(
        string $value, string $from, string $to
    ): bool
    {
        return ($value >= $from && $value <= $to);
    }
    
}