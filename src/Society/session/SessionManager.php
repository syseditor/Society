<?php

/*
 * Society - A Pocketmine plugin
 * Copyright © 2025 Dimitrios Kakagiannis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *          http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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