<?php
namespace Mako\Deadlockgametracker;

use PDO;
use PDOStatement;

class Database
{
    private PDO $connection;

    public function __construct(array $config, string $username, string $password = '')
    {
        // turns the config array into host=127.0.0.1;port=3307;dbname=deadlock
        $dsn = 'mysql:' . http_build_query($config, '', ';');

        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public function query(string $query, array $params = []): PDOStatement
    {
        $statement = $this->connection->prepare($query);

        // values get bound safely here instead of glued into the sql string
        $statement->execute($params);

        return $statement;
    }
}