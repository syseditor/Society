<?php

namespace Society\utils;

use pocketmine\utils\TextFormat;
use Society\guild\GuildRole;
use Society\party\PartyRole;

class Utils
{
    public static array $friendSlotPositions = [
      'FriendOne',
      'FriendTwo',
      'FriendThree',
      'FriendFour',
      'FriendFive',
      'FriendSix',
      'FriendSeven',
      'FriendEight',
      'FriendNine',
      'FriendTen'
    ];

    public static function roleToColor(PartyRole|GuildRole $role): string
    {
        return match ($role->getRoleName()) {
            "leader" => TextFormat::BLUE,
            "officer" => TextFormat::RED,
            "member" => TextFormat::GREEN,
            "founder" => TextFormat::GOLD,
            default => TextFormat::BLACK,
        };
    }
}