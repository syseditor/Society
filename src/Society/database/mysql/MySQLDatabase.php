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
    protected static mysqli $conn;
    protected static string $server;
    protected static string $username;
    protected static string $password;
    protected static string $dbName;
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

    protected static function error(): void
    {
        self::$logger->error('[~] Error: '.mysqli_error(self::$conn));
    }

    protected static function critical_error(string $message): void
    {
        self::error();
        self::$logger->error($message);
        self::$logger->emergency('[~] Forcing server shutdown to prevent further damage...');
        Society::getInstance()->getServer()->forceShutdown();
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
            self::critical_error("[~] Failed to connect to MySQL database: " . self::$conn->connect_error);
        }
    }

    public static function check(): void
    {
        # Database-based queries
        $checkDb = 'USE ' . self::$dbName . ';';
        $createDb = 'CREATE DATABASE ' . self::$dbName . ';'; #Requires database creation privileges

        # Table-based queries
        $checkFriend = 'SELECT PlayerId FROM Friends;';
        $checkGuild = 'SELECT PlayerId FROM Guilds;';
        $checkGuildInfo = 'SELECT GuildName FROM GuildsInfo;';
        $createFriend = "CREATE TABLE Friends (PlayerId varchar(255) NOT NULL , FriendOne varchar(255), FriendTwo varchar(255), FriendThree varchar(255), FriendFour varchar(255), FriendFive varchar(255), FriendSix varchar(255), FriendSeven varchar(255), FriendEight varchar(255), FriendNine varchar(255), FriendTen varchar(255), PRIMARY KEY (PlayerId));";
        $createGuild = "CREATE TABLE Guilds (PlayerId varchar(255) NOT NULL, GuildName varchar(255), GuildRole varchar(255), PRIMARY KEY (PlayerId));";
        $createGuildInfo = 'CREATE TABLE GuildsInfo (GuildName varchar(255) NOT NULL, GuildLevel int DEFAULT 1, GuildExp int DEFAULT 0, MaxAllowedMembers int DEFAULT 50, PRIMARY KEY (GuildName));';

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
            catch (mysqli_sql_exception)
            {
                self::critical_error('[~] Database could not be created: ' . mysqli_error(self::$conn));
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
            catch (mysqli_sql_exception)
            {
                self::critical_error('[~] Friends table could not be created: ' . mysqli_error(self::$conn));
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
            catch (mysqli_sql_exception)
            {
                self::critical_error('[~] Guilds table could not be created: ' . mysqli_error(self::$conn));
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
            catch (mysqli_sql_exception)
            {
                self::critical_error('[~] GuildsInfo table could not be created.');
            }
        }
    }

    public static function disconnect(): void
    {
        self::$conn->close();
    }

    public static function registerPlayer(Session $session): void
    {
        $name = $session->getPlayer()->getName();

        # Checks
        $checkPlayerInFriends = 'SELECT CASE WHEN EXISTS (SELECT PlayerId FROM Friends WHERE PlayerId = "'.$name.'") THEN TRUE ELSE FALSE END;';
        $checkPlayerInGuilds = 'SELECT CASE WHEN EXISTS (SELECT PlayerId FROM Guilds WHERE PlayerId = "'.$name.'") THEN TRUE ELSE FALSE END;';

        # Registers
        $registerInFriends = 'INSERT INTO Friends (PlayerId) VALUES ("'.$name.'");';
        $registerInGuilds = 'INSERT INTO Guilds (PlayerId) VALUES ("'.$name.'");';

        # First the Friends table...
        try
        {
            $query = mysqli_query(self::$conn, $checkPlayerInFriends);
            $results = $query->fetch_row();
            foreach ($results as $index => $result) { # The array has only one set of values-keys, so we don't really care about the code's structure
                if ($result == 0) mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Player '.$name.' is not registered in Friends table.";');
            }
        }
        catch (mysqli_sql_exception)
        {
            self::error();
            self::$logger->warning('[~] '.$name.' is not registered properly in the Database (Friends table). Quickly registering...');
            try
            {
                mysqli_query(self::$conn, $registerInFriends);
                self::$logger->notice('[~] Registered '.$name.' in the Database (Friends table).');
            }
            catch (mysqli_sql_exception)
            {
                self::critical_error('[~] Unable to register '.$name.' into Friends table.');
            }
        }

        #...and last the Guilds table
        try
        {
            $query = mysqli_query(self::$conn, $checkPlayerInGuilds);
            $results = $query->fetch_row();
            foreach ($results as $index => $result) { # The array has only one set of values-keys, so we don't really care about the code's structure
                if ($result == 0) mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Player '.$name.' is not registered in Guilds table.";');
            }
        }
        catch (mysqli_sql_exception)
        {
            self::error();
            self::$logger->warning('[~] '.$name.' is not registered properly in the Database (Guilds table). Quickly registering...');
            try
            {
                mysqli_query(self::$conn, $registerInGuilds);
                self::$logger->notice('[~] Registered '.$name.' in the Database (Guilds table).');
            }
            catch (mysqli_sql_exception)
            {
                self::critical_error('[~] Unable to register '.$name.' into Guilds table.');
            }
        }

        self::$logger->notice('[~] Player '.$name.' is properly registered in the Database.');
    }

    public static function loadPlayer(Session $session): void
    {
        $name = $session->getPlayer()->getName();
        $friendlist = [];

        # Getting info
        $getFriends = 'SELECT DISTINCT * FROM Friends WHERE PlayerId = "'.$name.'";';
        $getGuild = 'SELECT DISTINCT * FROM Guilds WHERE PlayerId = "'.$name.'";';

        # Get the friends
        try
        {
            $query = mysqli_query(self::$conn, $getFriends);
            $results = $query->fetch_assoc();
            if (empty($results)) mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Player\'s friendlist ('.$name.') does not exist (error during register?)";');
            else
            {
                foreach ($results as $assoc => $result)
                {
                    if (!strcmp($assoc, "PlayerId")) continue;
                    $friendlist[] = $result;
                }
                print_r($friendlist);
                $session->setFriendlist($friendlist);
            }
        }
        catch (mysqli_sql_exception)
        {
            self::critical_error('[~] Unable to gain the requested information.');
        }

        # Get the Guild
        try
        {
            $query = mysqli_query(self::$conn, $getGuild);
            $results = $query->fetch_assoc();
            if (empty($results)) mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Player\'s Guild field ('.$name.') does not exist (error during register?)";');
            else
            {
                $guildName = $results["GuildName"];
                $guild = GuildManager::getGuildByName($guildName); //lowercase check

                $roleName = $results["GuildRole"];
                $role = GuildManager::getGuildRoleByName($roleName); //lowercase check

                $session->setGuild($guild);
                $session->setGuildRole($role);
            }
        }
        catch (mysqli_sql_exception)
        {
            self::critical_error('[~] Unable to gain the requested information.');
        }
    }

    public static function registerGuild(Guild $guild): void
    {
        $name = $guild->getName();
        $level = $guild->getLevel();
        $exp = $guild->getExperiencePoints();
        $maxmembers = $guild->getMaxMembersAllowed();

        $checkQuery = 'SELECT CASE WHEN EXISTS (SELECT GuildName FROM GuildsInfo WHERE GuildName = "'.$name.'") THEN TRUE ELSE FALSE END;';
        $registerQuery = 'INSERT INTO GuildsInfo VALUES ("'.$name.'", '.$level.', '.$exp.', '.$maxmembers.');';

        try
        {
            $query = mysqli_query(self::$conn, $checkQuery);
            $results = $query->fetch_row();
            foreach($results as $index => $result)
            {
                if($result == 0) mysqli_query(self::$conn, 'SIGNAL SQLSTATE "40000" SET MESSAGE_TEXT = "Guild '.$name.' is not registered in GuildsInfo table.";');
            }
        }
        catch (mysqli_sql_exception)
        {
            self::error();
            self::$logger->warning('[~] '.$name.' guild is not registered properly in the Database (GuildsInfo table). Quickly registering...');
            try
            {
                mysqli_query(self::$conn, $registerQuery);
                self::$logger->notice('[~] Registered Guild '.$name.' in the Database (GuildsInfo table).');
            }
            catch (mysqli_sql_exception)
            {
                self::critical_error('[~] Unable to register Guild '.$name.' into GuildsInfo table.');
            }
        }
    }

    public static function loadGuilds(): void
    {
        $loadQuery = 'SELECT DISTINCT * FROM GuildsInfo;';

        try
        {
            $query = mysqli_query(self::$conn, $loadQuery);
            $results = $query->fetch_all();
            $total_guilds_loaded = 0;
            foreach($results as $index => $informationArray)
            {
                $guildName = $informationArray[0];
                $level = $informationArray[1];
                $exp = $informationArray[2];
                $maxmembers = $informationArray[3];
                $members = array(
                    'guildmaster' => "",
                    'coleader' => array(),
                    'officer' => array(),
                    'member' => array()
                );
                $selectMembersQuery = 'SELECT DISTINCT PlayerId, GuildRole FROM Guilds WHERE GuildName = "'.$guildName.'"';

                try
                {
                    $membersQuery = mysqli_query(self::$conn, $selectMembersQuery);
                    $membersAssoc = $membersQuery->fetch_all();
                    foreach($membersAssoc as $i => $memberInfo)
                    {
                        $members[$memberInfo[1]] = $memberInfo[0];
                    }
                }
                catch (mysqli_sql_exception)
                {
                    self::error();
                    self::$logger->notice('[~] Ignoring member registering process...'); //to be changed
                }

                $guild = new Guild($guildName, $members, $level, $exp, $maxmembers);
                GuildManager::registerGuild($guild);
                $total_guilds_loaded++;
            }
            self::$logger->notice("[~] Successfully loaded all registered guilds (Total of $total_guilds_loaded Guilds).");
        }
        catch (mysqli_sql_exception)
        {
            self::critical_error('[~] Unable to load the Guilds.');
        }
    }

    public static function removeGuild(Guild $guild): void
    {
        $name = $guild->getName();
        $members = $guild->getMembers();
        $deleteQuery = 'DELETE FROM GuildsInfo WHERE GuildName = "'.$name.'"';
        try
        {
            $query = mysqli_query(self::$conn, $deleteQuery);
            foreach($members as $role => $playerName)
            {
                self::update('Guilds', 'GuildName', $playerName, null);
                self::update('Guilds', 'GuildRole', $playerName, null);
            }
            self::$logger->notice("[~] Successfully deleted Guild $name from the database.");//to be removed
        }
        catch (mysqli_sql_exception)
        {
            self::critical_error("[~] Unable to delete Guild $name.");
        }
    }

    public static function update(string $table, string $column, string $condition, ?string $info): void
    {
        switch ($table){
            case 'Friends':
            case 'Guilds':
                if (!is_null($info)) $query = 'UPDATE '.$table.' SET '.$column.' = "'.$info.'" WHERE PlayerId = "'.$condition.'";';
                else $query = 'UPDATE '.$table.' SET '.$column.' = '.$info.' WHERE PlayerId = "'.$condition.'";';
                try
                {
                      mysqli_query(self::$conn, $query);
                      self::$logger->notice("[~] Successfully updated database"); //TO BE REMOVED
                }
                catch (mysqli_sql_exception)
                {
                    self::critical_error('[~] Unable to update the Database.');
                }
                break;
            case 'GuildsInfo':
                break;
            default:
                throw new RuntimeException("[~] Couldn't update the Database: Table out of range");
        }
    }
}