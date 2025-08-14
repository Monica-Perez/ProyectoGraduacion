<?php
class Database {
    private $host = DB_HOST;
    private $db = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbh;

    public function __construct() {
        try {
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db;
            $this->dbh = new PDO($dsn, $this->user, $this->pass);
            $this->dbh->exec("set names utf8");
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function conectar() {
        return $this->dbh;
    }
}
