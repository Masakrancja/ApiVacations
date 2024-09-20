<?php
/**
 * Vacations API
 * 
 * @package Vacations API
 * @version 1.0
 * @author Piotr Pietrzak
 * 
 */
declare(strict_types= 1);
namespace ApiVacations\Config;

use \ApiVacations\Debug\Dump;

/**
 * Class store credentials to database
 */
class DBConfig
{
  private static array $config = [
    "host"=> "localhost",
    "name"=> "db_name",
    "user"=> "db_user",
    "pass"=> "db_password"
  ];

  /**
   * Method give credentials to database
   */
  public static function getConfig(): array
  {
    if (file_exists(__DIR__ . '/config.php')) {
      $config = include __DIR__ . '/config.php';
      self::$config = $config;
    }
    return self::$config;
  }
}