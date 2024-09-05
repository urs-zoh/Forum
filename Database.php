<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

class Database
{
    private string $host;
    private string $dbname;
    private string $user;
    private string $password;
    private PDO $connection;

    public function __construct()
    {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->user = DB_USER;
        $this->password = DB_PASS;

        try {
            // Create DSN for the initial connection (without specifying the database)
            $dsn = "mysql:host=$this->host";

            // Connect to the MySQL server
            $this->connection = new PDO($dsn, $this->user, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create the database if it does not exist
            $sql = "CREATE DATABASE IF NOT EXISTS $this->dbname";
            $this->connection->exec($sql);

            // Reconnect to the newly created database
            $dsn = "mysql:host=$this->host;dbname=$this->dbname";
            $this->connection = new PDO($dsn, $this->user, $this->password);

            // Create tables within the database
            $this->create_table();
        } catch (PDOException $ex) {
            echo "Connection failed: " . $ex->getMessage();
        }
    }

    public function create_table()
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        avatar MEDIUMTEXT);

        CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)";
        $this->connection->exec($sql);
    }


    public function execute_query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}