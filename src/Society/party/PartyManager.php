<?php

namespace Society\party;

use Society\session\Session;

class PartyManager
{
    private static array $parties = [];
    public static array $rolePermissions = [
        "leader" => [
            "canKick" => true,
            "canInvite" => true
        ],
        "officer" => [
            "canKick" => false,
            "canInvite" => true
        ],
        "member" => [
            "canKick" => false,
            "canInvite" => false
        ]
    ];

    public static function getParties(): array
    {
        return self::$parties;
    }

    public static function getPartyByLeader(Session|string $leader): Party
    {
        if($leader instanceof Session) $leader = $leader->getName();
        return self::$parties[$leader];
    }

    public static function addParty(Party $party): bool
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
    }
}