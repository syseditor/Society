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

namespace Society\guild;

use Society\database\mysql\MySQLDatabase;

class GuildManager
{
    private static array $guilds = [];
    private static array $guildRoles = [];
    protected static array $guildRolePermissions = array(
        'member' => array(
            'canInvite' => false,
            'canKick' => false,
            'canPromote' => false,
            'guildAdmin' => false
        ),
        'officer' => array(
            'canInvite' => true,
            'canKick' => false,
            'canPromote' => false,
            'guildAdmin' => false
        ),
        'coleader' => array(
            'canInvite' => true,
            'canKick' => true,
            'canPromote' => true,
            'guildAdmin' => false
        ),
        'guildmaster' => array(
            'canInvite' => true,
            'canKick' => true,
            'canPromote' => true,
            'guildAdmin' => true
        )
    );

    public function __construct()
    {
        self::$guildRoles = array(
            'member' => new GuildRole('member', self::$guildRolePermissions['member']),
            'officer' => new GuildRole('officer', self::$guildRolePermissions['officer']),
            'coleader' => new GuildRole('coleader', self::$guildRolePermissions['coleader']),
            'guildmaster' => new GuildRole('guildmaster', self::$guildRolePermissions['guildmaster'])
        );
    }

    public static function initClass(): static
    {
        return new static();
    }

    public static function getGuilds(): array
    {
        return self::$guilds;
    }

    public static function getGuildByName(?string $name): ?Guild
    {
        return is_null($name) ? null : self::$guilds[$name];
    }

    public static function getGuildRoleByName(?string $name): ?GuildRole
    {
        return is_null($name) ? null : self::$guildRoles[$name];
    }

    public static function registerGuild(Guild $guild): void
    {
        $guildName = strtolower($guild->getName());
        self::$guilds[$guildName] = $guild;
    }

    public static function removeGuild(Guild $guild): void
    {
        MySQLDatabase::removeGuild($guild);
        unset(self::$guilds[strtolower($guild->getName())]);
    }

    public static function guildExists(string $name): bool
    {
        $name = strtolower($name);
        return array_key_exists($name, self::$guilds);
    }
}