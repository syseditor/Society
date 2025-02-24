<?php

namespace Society\guild;

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

    public static function getGuildRoleByName(string $name): GuildRole
    {
        return self::$guildRoles[$name];
    }

    public static function registerGuild(Guild $guild): void
    {
        $guildName = strtolower($guild->getName());
        self::$guilds[$guildName] = $guild;
    }
}