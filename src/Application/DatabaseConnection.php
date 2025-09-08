<?php

declare(strict_types=1);

namespace LightPHPF\Core\Application;

use PDO;

final class DatabaseConnection
{
    private static ?self $instance = null;

    private readonly string $id;
    private readonly string $host;
    private readonly int $port;
    private readonly string $dbconnect;
    private readonly string $dbname;
    private readonly string $username;
    private readonly string $password;

    private ?PDO $pdo = null;
    private \PDOStatement|bool $statement;

    public function __construct(array $config)
    {
        $this->host = $config['host'];
        $this->port = (int) $config['port'];
        $this->dbname = $config['database_name'];
        $this->dbconnect = $config['database_connect'];
        $this->username = $config['username'];
        $this->password = $config['password'];

        // Generate a unique ID for each connection
        $this->id = uniqid(uniqid('conn_', true));
        dump("connection id: {$this->id}");

        $this->connect();
    }

    /**
     * @details Private __clone to prevent cloning
     *
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * @param  array $config
     * @return self
     */
    public static function getInstance(array $config): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * @return \PDO;
     */
    public function connect()
    {
        try {
            if (is_null($this->pdo)) {
                $this->pdo = new PDO(
                    "{$this->dbconnect}:host={$this->host};port={$this->port};dbname={$this->dbname}",
                    $this->username,
                    $this->password
                );

                // Set PDO options for error handling and security
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                dump("database-{$this->dbconnect}-connected!");
            }

            return $this->pdo;
        } catch (\PDOException $e) {
            dump("Database Connection failed: " . $e->getMessage());
        }
    }

    /**
     * @param  string $sql
     *
     * @return void
     */
    public function query(string $sql): void
    {
        $this->statement = $this->getPdo()->prepare($sql);
    }

    /**
     * @param  int|string $parameter
     * @param  mixed $value
     * @param  ?int $type
     *
     * @return void
     */
    public function bind(int|string $parameter, mixed $value, ?int $type = null): void
    {
        switch (is_null($type)) {
            case is_int($value):
                $type = PDO::PARAM_INT;
                break;
            case is_bool($value):
                $type = PDO::PARAM_BOOL;
                break;
            case is_null($value):
                $type = PDO::PARAM_NULL;
                break;
            default:
                $type = PDO::PARAM_STR;
        }

        $this->statement->bindValue($parameter, $value, $type);
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        return $this->statement->execute();
    }

    /**
     * @return array
     */
    public function resultSet(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @return \stdClass
     */
    public function single(): \stdClass
    {
        return $this->statement->fetch(PDO::FETCH_OBJ);
    }

    /**
     * @return int
     */
    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * @return \PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function getConnectionId(): string
    {
        return $this->id;
    }
}
