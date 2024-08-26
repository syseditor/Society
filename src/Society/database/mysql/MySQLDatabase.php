<?php

namespace Society\database\mysql;

use pocketmine\utils\SingletonTrait;

use Society\database\Database;
use Society\Society;
use mysqli, mysqli_sql_exception;

class MySQLDatabase extends Database
{
    use SingletonTrait;

    private mysqli $conn;
    private string $server;
    private string $username;
    private string $password;
    private string $dbName;
    private $logger;

    public function __construct()
    {
        self::setInstance($this);

        $settings = Society::getInstance()->getSettings();
        $this->logger = Society::getInstance()->getLogger();

        $this->server = $settings->get('servername');
        $this->username = $settings->get('username');
        $this->password = $settings->get('password');
        $this->dbName = $settings->get('databaseName');
    }

    public function connect(): void
    {
        try
        {
            $this->conn = new mysqli($this->server, $this->username, $this->password);
            $this->logger->notice('[~] Successfully connected to MySQL Server.');
        }
        catch (mysqli_sql_exception)
        {
            $this->logger->critical("[~] Failed to connect to MySQL database: " . $this->conn->connect_error);
            $this->logger->emergency('[~] Forcing server shutdown to prevent further damage...');
            Society::getInstance()->getServer()->forceShutdown();
        }
    }

    public function check(): void
    {
        $check = 'USE ' . $this->dbName . ';';
        $creation = 'CREATE DATABASE ' . $this->dbName . ';';

        try
        {
            mysqli_query($this->conn, $check);
            $this->logger->notice('[~] Database exists, all working fine.');
        }
        catch (mysqli_sql_exception)
        {
            $this->logger->warning('[~] Database does not exist, creating one ASAP...');
            try
            {
                mysqli_query($this->conn, $creation);
                $this->logger->notice('[~] Database created, all working fine.');
            }
            catch (mysqli_sql_exception) {
                $this->logger->error('[~] Database could not be created: ' . mysqli_error($this->conn));
                Society::getInstance()->getServer()->forceShutdown();
            }
        }
    }

    public function disconnect(): void
    {
        $this->conn->close();
    }
}