<?php

namespace Society\guild;

class GuildManager
{
    private static array $guilds = [];
    private static array $guildRoles = [];

    public function __construct()
    {
        self::$guildRoles = array(
            'member' => new GuildRole("member"),
            'officer' => new GuildRole("officer"),
            'leader' => new GuildRole("leader"),
            'founder' => new GuildRole("founder")
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

    public static function getGuildByName(null|string $name): null|Guild
    {
        if (is_null($name)) return null;
        return self::$guilds[$name];
    }

    public static function getGuildRoleByName(null|string $name): null|GuildRole
    {
        if (is_null($name)) return null;
        return self::$guildRoles[$name];
    }
}