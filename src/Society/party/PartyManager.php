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

namespace Society\party;

use Society\session\Session;

class PartyManager
{
    private static array $parties = [];
    public static array $rolePermissions = [
        "leader" => [
            "canKick" => true,
            "canInvite" => true,
            "canDisband" => true
        ],
        "officer" => [
            "canKick" => true,
            "canInvite" => true,
            "canDisband" => false
        ],
        "member" => [
            "canKick" => false,
            "canInvite" => false,
            "canDisband" => false
        ]
    ];
    public static array $roles = [];

    public function __construct()
    {
        self::$roles = array(
            "member" => new PartyRole("member"),
            "officer" => new PartyRole("officer"),
            "leader" => new PartyRole("leader")
        );
    }

    public static function initClass(): self
    {
        return new static();
    }

    public static function getParties(): array
    {
        return self::$parties;
    }

    public static function getPartyByLeader(Session|string $leader): Party
    {
        if($leader instanceof Session) $leader = $leader->getName();
        return self::$parties[$leader];
    }

    public static function registerParty(Party $party): bool
    {
        $leader = $party->getLeader()->getName();
        if(array_key_exists($leader, self::$parties))
        {
            if(is_null(self::$parties[$leader]))
            {
                self::$parties[$leader] = $party;
                return true;
            }
            return false;
        }
        else
        {
            self::$parties[$leader] = $party;
            return true;
        }
    }

    public static function removeParty(Party $party): void
    {
        $leader = $party->getLeader()->getName();
        self::$parties[$leader] = null;
        unset($party);
    }
}