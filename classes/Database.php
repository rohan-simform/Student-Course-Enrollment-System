<?php

require_once __DIR__.'./../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();
/**
 * Handles database connection setup.
 */
class Database {
    /**
     * Active database connection.
     *
     * @var mysqli|null
     */
    public $conn;

    /**
     * Create and return database connection.
     *
     * @return mysqli
     */
    public function connect() {
        $this->conn = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_NAME'],
            $_ENV['DB_PORT']
        );

        if ($this->conn->connect_error) {
            exit($this->conn->connect_error);
        }

        return $this->conn;
    }
}
