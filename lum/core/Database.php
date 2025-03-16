<?php
namespace LUM\core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct(array $config)
    {
        $this->pdo = $this->createConnection($config);
    }

    private function createConnection(array $config)
    {
        $driver = $config['driver'];
        switch ($driver) {
            case 'mysql':
                $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
                break;
            case 'pgsql':
                $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
                break;
            case 'sqlite':
                $dsn = "sqlite:{$config['sqlite_path']}";
                break;
            default:
                throw new \InvalidArgumentException("Unsupported database driver: {$driver}");
        }

        try {
            $pdo = new PDO($dsn, $config['username'] ?? null, $config['password'] ?? null);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(array $config)
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getPdo()
    {
        return $this->pdo;
    }
}
