<?php

namespace Society\guild;

use Society\database\mysql\MySQLDatabase;
use Society\session\Session;
use Society\session\SessionManager;
use Society\utils\Constants;

class Guild
{
    private string $name;
    private int $level;
    private int $exp;
    private int $maxMembersAllowed;
    private array $members;
    private bool $permissionToDisbandGiven;

    public function __construct(string $name, array $members, int $level = 1, int $exp = 0, int $maxAllowedMembers = 50)
    {
        $this->name = $name;
        $this->level = $level;
        $this->exp = $exp;
        $this->maxMembersAllowed = $maxAllowedMembers;
        $this->members = $members;
        $this->permissionToDisbandGiven = false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getExperiencePoints(): int
    {
        return $this->exp;
    }

    public function getMaxMembersAllowed(): int
    {
        return $this->maxMembersAllowed;
    }


    public function getMembers(): array
    {
        return $this->members;
    }

    public function getMemberCount(): int
    {
        return count($this->getMembers(), 1) - 4 + 1; //4 rank titles, 1 guildmaster (it's a string, NOT A 1x1 array)
    }

    public function wasGivenPermissionToDisband(): bool
    {
        return $this->permissionToDisbandGiven;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function setExperiencePoints(int $exp): void
    {
        $this->exp = $exp;
    }

    public function setMaxMembersAllowed(int $maxMembersAllowed): void
    {
        $this->maxMembersAllowed = $maxMembersAllowed;
    }

    public function setPermissionToDisband(bool $option): void
    {
        $this->permissionToDisbandGiven = $option;
    }

    public function refresh(): void //refreshes the current guild status
    {
        $this->updateMaxMembersAllowed();
    }

    protected function updateMaxMembersAllowed(): void //guildmaster must be online => only called by themselves
    {
        $guildmaster = SessionManager::getSessionByName($this->members["guildmaster"]);
        $oldMax = $this->maxMembersAllowed;

        if($guildmaster->hasPermission("society.guild.twohundred")) $this->setMaxMembersAllowed(200);
        else if($guildmaster->hasPermission("society.guild.hundred")) $this->setMaxMembersAllowed(100);
        else $this->setMaxMembersAllowed(50);

        if($oldMax !== $this->maxMembersAllowed)
            MySQLDatabase::update("GuildsInfo", "MaxAllowedMembers", $this->getName(), $this->maxMembersAllowed);
    }

    public function removeMember(string $member, string $cause): void
    {
        MySQLDatabase::update('Guilds', 'GuildName', $member, null);
        MySQLDatabase::update('Guilds', 'GuildRole', $member, null);
        if(array_key_exists($member, SessionManager::getSessions()))
        {
            $session = SessionManager::getSessionByName($member);
            $session->setGuildRole(null);
            $session->setGuild(null);
            $session->setCurrentChat(Constants::CHAT_GLOBAL);
            $session->sendMessage("[Guild] " . $cause);
        }
    }

    public function disband(): void
    {
        if($this->wasGivenPermissionToDisband()) //double checking
        {
            $members = $this->getMembers();

            foreach($members as $rank => $values)
            {
                if(is_string($values)) $this->removeMember($values, "The Guild was disbanded.");
                else
                {
                    foreach($values as $member) $this->removeMember($member, "The Guild was disbanded.");
                }
            }

            MySQLDatabase::removeGuild($this);
            GuildManager::removeGuild($this);
        }
    }

    public function broadcastMessage(string $message): void
    {
        foreach($this->members as $rank => $memberArray)
        {
            if(is_string($memberArray))
            {
                if(array_key_exists($memberArray, SessionManager::getSessions()))
                {
                    $session = SessionManager::getSessionByName($memberArray);
                    $session->sendMessage($message);
                }
            }
            else
            {
                foreach($memberArray as $member)
                {
                    if(array_key_exists($member, SessionManager::getSessions()))
                    {
                        $session = SessionManager::getSessionByName($member);
                        $session->sendMessage($message);
                    }
                }
            }
        }
    }

    public function isGuildMember(string $player): bool
    {
        $members = array();
        foreach($this->members as $rank => $values)
        {
            if(is_string($values)) $members[] = $values;
            else $members = array_merge($members, $values);
        }

        return in_array($player, $members);
    }

    public function isMember(string $player): bool
    {
        return in_array($player, $this->members["member"]);
    }

    public function isOfficer(string $player): bool
    {
        return in_array($player, $this->members["officer"]);
    }

    public function isColeader(string $player): bool
    {
        return in_array($player, $this->members["coleader"]);
    }
}