<?php

namespace Society\session;

use pocketmine\player\Player;

class SessionManager
{
    private static array $sessions = [];

    public static function getSessions(): array
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
        $session = new Session($player);

        self::$sessions[$name] = $session;
    }

    public static function closeSession(Session $session): void
    {
        $new_array = array_diff(self::$sessions, [$session]);
        self::$sessions = $new_array;
    }
}