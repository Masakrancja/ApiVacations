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

/**
 * Class store credentials to database
 */
class DBConfig
{
  private static array $config = [
    "host"=> "localhost",
    "name"=> "Vacations",
    "user"=> "root",
    "pass"=> "dupablada2000"
  ];

  /**
   * Method give credentials to database
   */
  public static function getConfig(): array
  {
    return self::$config;
  }
}