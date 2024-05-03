<?php
declare(strict_types= 1);
namespace ApiVacations\Config;

class DBConfig
{
  private static array $config = [
    "host"=> "localhost",
    "name"=> "Vacations",
    "user"=> "root",
    "pass"=> "dupablada2000"
  ];

  public static function getConfig(): array
  {
    return self::$config;
  }
}