<?php
class Database {
    private $host = "mysql";
    private $username = "root";
    private $password = "pass";
    private $dbname = "enrollment_db";
    private $port = "3306";

    public $conn;

    public function connect(){
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->dbname,
            $this->port
        );

        if($this->conn->connect_error){
            die($this->conn->connect_error);
        }

        return $this->conn;
    }
}