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
            "leader", "coleader" => TextFormat::BLUE,
            "officer" => TextFormat::RED,
            "member" => TextFormat::GREEN,
            "guildmaster" => TextFormat::GOLD,
            default => TextFormat::BLACK,
        };
    }
}