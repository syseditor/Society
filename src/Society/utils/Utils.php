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
        switch($role->getRoleName())
        {
            case "leader":
                return TextFormat::BLUE;
            case "officer":
                return TextFormat::RED;
            case "member":
                return TextFormat::GREEN;
            case "founder":
                return TextFormat::GOLD;
            default: return TextFormat::BLACK;
        }
    }
}