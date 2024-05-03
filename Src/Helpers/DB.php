<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;

use ApiVacations\Exceptions\ConfigExcepion;
use ApiVacations\Exceptions\DatabaseException;

final class DB
{
    private static ?DB $instance = null;
    private array $config;
    public \PDO $connect;
    
    private function __construct(array $config)
    {
        $this->config = $config;
        $this->validate();
        $this->connect();

    }

    public static function getInstance(array $config): DB
    {
        if (self::$instance === null) {
            return new self($config);
        }
        return self::$instance;
    }

    private function validate(): void
    {
        $correctKeys = ['host', 'user', 'name', 'pass'];
        foreach ($this->config as $key => $config) {
            if (empty($config) OR !in_array($key, $correctKeys)) {
                throw new ConfigExcepion('Error database credentials', 500);
            }
        }
    }

    private function connect(): void
    {
        try {
            $dns = 'mysql:dbname=' . $this->config['name'] . ';host=' . $this->config['host'];
            $this->connect = new \PDO(
                $dns,
                $this->config['user'],
                $this->config['pass'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]
            );            
        }
        catch (\PDOException $e) {
            throw new DatabaseException('Błąd połączenia z bazą danych', 500);
        }  

    }

}