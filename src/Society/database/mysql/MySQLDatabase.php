<?php

namespace Society\database\mysql;

use Society\database\Database;
use Society\guild\GuildManager;
use Society\session\Session;
use Society\guild\Guild;
use Society\Society;
use mysqli, mysqli_sql_exception;
use RuntimeException;

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
        $checkFriend = 'SELECT PlayerId FROM Friends;'; #"SELECT EXISTS (SELECT 'Friends' FROM information_schema.tables);";
        $checkGuild = 'SELECT PlayerId FROM Guilds;'; #"SELECT EXISTS (SELECT 'Guilds' FROM information_schema.tables);";
        $checkGuildInfo = 'SELECT GuildName FROM GuildsInfo;';
        $createFriend = "CREATE TABLE Friends (PlayerId varchar(255) NOT NULL , FriendOne varchar(255), FriendTwo varchar(255), FriendThree varchar(255), FriendFour varchar(255), FriendFive varchar(255), FriendSix varchar(255), FriendSeven varchar(255), FriendEight varchar(255), FriendNine varchar(255), FriendTen varchar(255), PRIMARY KEY (PlayerId));";
        $createGuild = "CREATE TABLE Guilds (PlayerId varchar(255) NOT NULL, GuildName varchar(255), GuildRole varchar(255), PRIMARY KEY (PlayerId));";
        $createGuildInfo = 'CREATE TABLE GuildsInfo (GuildName varchar(255) NOT NULL, GuildLeader varchar(255) NOT NULL, GuildLevel int DEFAULT 0, GuildExp int DEFAULT 0);';

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

        # Checking if the GuildsInfo table exists
        try
        {
            mysqli_query(self::$conn, $checkGuildInfo);
            self::$logger->notice('[~] GuildsInfo table exists, continuing.');
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->warning('[~] GuildsInfo table does not exist, creating one ASAP...');
            try
            {
                mysqli_query(self::$conn, $createGuildInfo);
                self::$logger->notice('[~] GuildsInfo table was created, continuing.');
            }
            catch (mysqli_sql_exception) {
                self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
                self::$logger->error('[~] GuildsInfo table could not be created.');
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
    public static function registerPlayer(Session $session): void
    {
        $name = $session->getPlayer()->getName();
        $id = $session->getPlayer()->getUniqueId()->getInteger();

        # Checks
        $checkPlayerInFriends = 'SELECT CASE WHEN EXISTS (SELECT PlayerId FROM Friends WHERE PlayerId = "'.$id.'") THEN TRUE ELSE FALSE END;';
        $checkPlayerInGuilds = 'SELECT CASE WHEN EXISTS (SELECT PlayerId FROM Guilds WHERE PlayerId = "'.$id.'") THEN TRUE ELSE FALSE END;';

        # Registers
        $registerInFriends = 'INSERT INTO Friends (PlayerId) VALUES ("'.$id.'");';
        $registerInGuilds = 'INSERT INTO Guilds (PlayerId) VALUES ("'.$id.'");';

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

        self::$logger->notice('[~] Player '.$name.' with UUID "'.$id.'" is properly registered in the Database.');
    }

    public static function loadPlayer(Session $session): void
    {
        $name = $session->getPlayer()->getName();
        $id = $session->getPlayer()->getUniqueId()->getInteger();
        $friendlist = [];

        # Getting info
        $getFriends = 'SELECT DISTINCT * FROM Friends WHERE PlayerId = "'.$id.'";';
        $getGuild = 'SELECT DISTINCT * FROM Guilds WHERE PlayerId = "'.$id.'";';

        # Get the friends
        try
        {
            $query = mysqli_query(self::$conn, $getFriends);
            $results = $query->fetch_assoc();
            print_r($results);
            if (empty($results))
            {
                mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Player\'s friendlist ('.$name.') does not exist (error during register?)";');
            } else
            {
                foreach ($results as $assoc => $result)
                {
                    if ($assoc == "PlayerId") continue;
                    $friendlist[] = $result;
                }
                print_r($friendlist);
                $session->setFriendlist($friendlist);
            }
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
            self::$logger->error('[~] Unable to gain the requested information.');
            self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
            Society::getInstance()->getServer()->forceShutdown();
        }

        # Get the Guild
        try
        {
            $query = mysqli_query(self::$conn, $getGuild);
            $results = $query->fetch_assoc();
            print_r($results);
            if (empty($results))
            {
                mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Player\'s Guild field ('.$name.') does not exist (error during register?)";');
            } else
            {
                $guild = $results["GuildName"]; echo $guild;
                $role = $results["GuildRole"]; echo $role;
                $session->setGuild(GuildManager::getGuildByName($guild));
                $session->setGuildRole(GuildManager::getGuildRoleByName($role));
            }
        }
        catch (mysqli_sql_exception)
        {
            self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
            self::$logger->error('[~] Unable to gain the requested information.');
            self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
            Society::getInstance()->getServer()->forceShutdown();
        }
    }

    public static function registerGuild(Guild $guild): void
    {
        //TODO
    }

    public static function loadGuilds(): void
    {
        //TODO
    }

    public static function insert(string $table, string $column, string $info, ?Session $session = null): void
    {
        switch ($table){
            case 'Friends':
            case 'Guilds':
                if (is_null($session)) throw new RuntimeException("[~] Couldn't update the Database: Session is null");
                $id = $session->getPlayer()->getUniqueId()->getInteger();
                $query = 'UPDATE '.$table.' SET '.$column.' = "'.$info.'" WHERE PlayerId = "'.$id.'";';
                try
                {
                      mysqli_query(self::$conn, $query);
                      self::$logger->notice("[~] Successfully updated database"); //TO BE REMOVED
                }
                catch (mysqli_sql_exception)
                {
                    self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
                    self::$logger->error('[~] Unable to update the Database.');
                    self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
                    Society::getInstance()->getServer()->forceShutdown();
                }
                break;
            case 'GuildsInfo':
                break;
            default:
                throw new RuntimeException("[~] Couldn't update the Database: Table out of range");
        }
    }

    public static function checkFriendUniqueness(string $id, Session $session): bool
    {
        $id = $session->getPlayer()->getUniqueId()->getInteger();
        //continue
        return false;
    }
}