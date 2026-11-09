
<?php

class Database
{
    private PDO $connection;

    public function __construct()
    {
        $dbPath = __DIR__ . '/../database/tropa.db';

        try {
            $this->connection = new PDO('sqlite:' . $dbPath);

            $this->connection->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $this->connection->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

            $this->connection->exec('PRAGMA foreign_keys = ON');

        } catch (PDOException $e) {
            exit('Ошибка подключения к базе данных.');
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
