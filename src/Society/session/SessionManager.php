<?php

namespace Society\session;

use pocketmine\player\Player;

use Society\database\mysql\MySQLDatabase;

class SessionManager
{
    private static array $sessions = [];

    public static function getSessions(): array #MIGHT REMOVE IF NOT NEEDED
    {
        return self::$sessions;
    }

    public static function getSessionByName(string $name): Session
    {
        return self::$sessions[$name];
    }

    public static function openSession(Player $player): void
    {
        $name = $player->getName();
        $session = new Session($player, null);

        MySQLDatabase::registerPlayer($session); #If not already registered in the database
        MySQLDatabase::loadPlayer($session);

        self::$sessions[$name] = $session;
    }

    public static function closeSession(Session $session): void
    {
        $name = $session->getPlayer()->getName();
        unset(self::$sessions[$name]);
    }
}