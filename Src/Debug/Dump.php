<?php
declare(strict_types=1);
namespace ApiVacations\Debug;

class Dump
{
  public static function dump(mixed $data): void
  {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
  }
}