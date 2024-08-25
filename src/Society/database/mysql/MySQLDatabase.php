<?php

namespace Society\database\mysql;

use Society\database\Database;
use Society\Society;
use mysqli;

class MySQLDatabase extends Database
{
    private mysqli $conn;
    private string $server;
    private string $username;
    private string $password;
    private $logger;

    public function __construct()
    {
        $settings = Society::getInstance()->getSettings();
        $this->server = $settings->get('servername');
        $this->username = $settings->get('username');
        $this->password = $settings->get('password');
        $this->logger = Society::getInstance()->getLogger();
    }

    public function connect(): void
    {
        $this->conn = new mysqli($this->server, $this->username, $this->password);
        if($this->conn->connect_error)
        {
            $this->logger->error("[~] Failed to connect to MySQL database: " . $this->conn->connect_error);
            Society::getInstance()->getServer()->forceShutdown();
        } else {
            $this->logger->notice('[~] Successfully connected to MySQL database');
        }
    }

    public function check(string $dbName): void
    {
        $check = 'USE Society';
        $creation = 'CREATE DATABASE Society';

        $result = mysqli_query($this->conn, $check);
        if($result == 1)
        {
            $this->logger->warning('[~] Database does not exist, creating one ASAP...');
            if(mysqli_query($this->conn, $creation))
            {
                $this->logger->notice('[~] Database created, all working fine.');
            } else {
                $this->logger->error('[~] Database could not be created: ' . mysqli_error($this->conn));
            }
        } else {
            $this->logger->notice('[~] Database exists, all working fine.');
        }
    }

    public function disconnect(): void
    {
        $this->conn->close();
    }
}