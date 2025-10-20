<?php
class DbConnector {
    private $servername;
    private $username;
    private $password;
    private $dbname;

    public function __construct(string $servername, string $username, string $password, string $dbname) {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
    }

    public function prepare(string $query): PDOStatement {
        $connection = new PDO("mysql:host={$this->servername};dbname={$this->dbname}", $this->username, $this->password);
        return $connection->prepare($query);
    }
}
