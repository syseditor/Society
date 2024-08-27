<?php

namespace Society\database\mysql;

use Society\session\Session;
use Society\database\Database;
use Society\Society;
use mysqli, mysqli_sql_exception;

class MySQLDatabase extends Database
{
    private static mysqli $conn;
    private static string $server;
    private static string $username;
    private static string $password;
    private static string $dbName;
    private static $logger;

    public function __construct()
    {
        $settings = Society::getInstance()->getSettings();
        self::$logger = Society::getInstance()->getLogger();

        self::$server = $settings->get('servername');
        self::$username = $settings->get('username');
        self::$password = $settings->get('password');
        self::$dbName = $settings->get('databaseName');
    }

    public static function initClass(): static
    {
        return new static();
    }

    public static function connect(): void
    {
        try
        {
            self::$conn = new mysqli(self::$server, self::$username, self::$password);
            self::$logger->notice('[~] Successfully connected to MySQL Server.');
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
            self::$logger->critical("[~] Failed to connect to MySQL database: " . self::$conn->connect_error);
            self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
            Society::getInstance()->getServer()->forceShutdown();
        }
    }

    public static function check(): void
    {
        # Database-based queries
        $checkDb = 'USE ' . self::$dbName . ';';
        $createDb = 'CREATE DATABASE ' . self::$dbName . ';'; #Requires database creation privileges

        # Table-based queries
        $checkFriend = 'SELECT PlayerName FROM Friends;'; #"SELECT EXISTS (SELECT 'Friends' FROM information_schema.tables);";
        $checkGuild = 'SELECT PlayerName FROM Guilds'; #"SELECT EXISTS (SELECT 'Guilds' FROM information_schema.tables);";
        $createFriend = "CREATE TABLE Friends (PlayerName varchar(255) NOT NULL , FriendOne varchar(255), FriendTwo varchar(255), FriendThree varchar(255), FriendFour varchar(255), FriendFive varchar(255), FriendSix varchar(255), FriendSeven varchar(255), FriendEight varchar(255), FriendNine varchar(255), FriendTen varchar(255));";
        $createGuild = "CREATE TABLE Guilds (PlayerName varchar(255) NOT NULL, GuildName varchar(255))";

        # Checking if the database exists
        try
        {
            mysqli_query(self::$conn, $checkDb);
            self::$logger->notice('[~] Database exists, continuing.');
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->warning('[~] Database does not exist, creating one ASAP...');
            try
            {
                mysqli_query(self::$conn, $createDb);
                self::$logger->notice('[~] Database was created, continuing.');
            }
            catch (mysqli_sql_exception) {
                self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
                self::$logger->error('[~] Database could not be created: ' . mysqli_error(self::$conn));
                self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
                Society::getInstance()->getServer()->forceShutdown();
            }
        }

        # Checking if the Friends table exists
        try
        {
            mysqli_query(self::$conn, $checkFriend);
            self::$logger->notice('[~] Friends table exists, continuing.');
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->warning('[~] Friends table does not exist, creating one ASAP...');
            try
            {
                mysqli_query(self::$conn, $createFriend);
                self::$logger->notice('[~] Friends table was created, continuing.');
            }
            catch (mysqli_sql_exception) {
                self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
                self::$logger->error('[~] Friends table could not be created: ' . mysqli_error(self::$conn));
                self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
                Society::getInstance()->getServer()->forceShutdown();
            }
        }

        # Checking if the Guilds table exists
        try
        {
            mysqli_query(self::$conn, $checkGuild);
            self::$logger->notice('[~] Guilds table exists, continuing.');
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->warning('[~] Guilds table does not exist, creating one ASAP...');
            try
            {
                mysqli_query(self::$conn, $createGuild);
                self::$logger->notice('[~] Guilds table was created, continuing.');
            }
            catch (mysqli_sql_exception) {
                self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
                self::$logger->error('[~] Guilds table could not be created: ' . mysqli_error(self::$conn));
                self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
                Society::getInstance()->getServer()->forceShutdown();
            }
        }
    }

    public static function disconnect(): void
    {
        self::$conn->close();
    }

    # Register the player
    public static function register(Session $session): void
    {
        $name = $session->getPlayer()->getName();

        # Checks
        $checkPlayerInFriends = 'SELECT CASE WHEN EXISTS (SELECT PlayerName FROM Friends WHERE PlayerName = "'.$name.'") THEN TRUE ELSE FALSE END;';
        $checkPlayerInGuilds = 'SELECT CASE WHEN EXISTS (SELECT PlayerName FROM Guilds WHERE PlayerName = "'.$name.'") THEN TRUE ELSE FALSE END;';

        # Registers
        $registerInFriends = 'INSERT INTO Friends (PlayerName) VALUES ("'.$name.'")';
        $registerInGuilds = 'INSERT INTO Guilds (PlayerName) VALUES ("'.$name.'")';

        # First the Friends table...
        try
        {
            $query = mysqli_query(self::$conn, $checkPlayerInFriends);
            $results = $query->fetch_row();
            foreach ($results as $index => $result) { # The array has only one set of values-keys, so we don't really care about the code's structure
                if ($result == 0) {
                    mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Player '.$name.' is not registered in Friends table.";');
                }
            }
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
            self::$logger->warning('[~] '.$name.' is not registered properly in the Database (Friends table). Quickly registering...');
            try
            {
                mysqli_query(self::$conn, $registerInFriends);
                self::$logger->notice('[~] Registered '.$name.' in the Database (Friends table).');
            }
            catch (mysqli_sql_exception)
            {
                self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
                self::$logger->error('[~] Unable to register '.$name.' into Friends table.');
                self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
                Society::getInstance()->getServer()->forceShutdown();
            }
        }

        #...and last the Guilds table
        try
        {
            $query = mysqli_query(self::$conn, $checkPlayerInGuilds);
            $results = $query->fetch_row();
            foreach ($results as $index => $result) { # The array has only one set of values-keys, so we don't really care about the code's structure
                if ($result == 0) {
                    mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Player '.$name.' is not registered in Guilds table.";');
                }
            }
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
            self::$logger->warning('[~] '.$name.' is not registered properly in the Database (Guilds table). Quickly registering...');
            try
            {
                mysqli_query(self::$conn, $registerInGuilds);
                self::$logger->notice('[~] Registered '.$name.' in the Database (Guilds table).');
            }
            catch (mysqli_sql_exception)
            {
                self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
                self::$logger->error('[~] Unable to register '.$name.' into Guilds table.');
                self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
                Society::getInstance()->getServer()->forceShutdown();
            }
        }

        self::$logger->notice('[~] Player '.$name.' is properly registered in the Database.');
    }

    public static function load(Session $session): void
    {
        //TODO
    }
}