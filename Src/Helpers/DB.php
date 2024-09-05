<?php
declare(strict_types=1);
namespace ApiVacations\Helpers;

use ApiVacations\Exceptions\ConfigExcepion;
use ApiVacations\Exceptions\DatabaseException;
use ApiVacations\Helpers\Logger;

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

    public function getConn(): \PDO
    {
        return $this->connect;
    }

    public function selectProcess(
        string $sql,
        array $params = [],
        $method = 'fetch'
    ): ?array {
        try {
            $stmt = $this->getConn()->prepare($sql);
            foreach ($params as $param) {
                $stmt->bindValue($param['key'], $param['value'], $param['type']);
            }
            $stmt->execute();
            if ($method == 'fetch') {
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                return ($row) ? $row : null;
            } elseif ($method == 'fetchAll') {
                return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?? [];
            } else {
                Logger::error('Error method. Only fetch and fetchAll', ['Method' => 'DB::selectProcess', 'File' => 'db.php']);
                throw new ConfigExcepion('Server error', 500);
            }
        } catch (\PDOException $e) {
            Logger::error($e->getMessage() . ' - sql query: ' . $sql, ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }

    private function validate(): void
    {
        $correctKeys = ['host', 'user', 'name', 'pass'];
        foreach ($this->config as $key => $config) {
            if (empty($config) or !in_array($key, $correctKeys)) {
                Logger::error('Error configuration file', ['Method' => 'DB::validate', 'File' => 'db.php']);
                throw new ConfigExcepion('Server error', 500);
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
        } catch (\PDOException $e) {
            Logger::error($e->getMessage(), ['Line' => $e->getLine(), 'File' => $e->getFile()]);
            throw new DatabaseException('Server error', 500);
        }
    }
}