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

    public function getMaxTotalMembersAllowed(): int
    {
        return $this->maxMembersAllowed;
    }

    public function getMaxOfficersAllowed(): int //TODO: derive this from settings.yml
    {
        if($this->maxMembersAllowed == 50) $result = 3;
        else if($this->maxMembersAllowed == 100) $result = 5;
        else $result = 7;

        return $result;
    }

    public function getMaxColeadersAllowed(): int //TODO: derive this from settings.yml
    {
        if($this->maxMembersAllowed == 50) $result = 2;
        else if($this->maxMembersAllowed == 100) $result = 3;
        else $result = 4;

        return $result;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function getTotalMemberCount(): int
    {
        return count(array_filter($this->getMembers(), fn($value) => !empty($value) , ARRAY_FILTER_USE_BOTH), 1);
    }

    public function getOfficerCount(): int
    {
        return count(array_filter($this->getMembers()["officer"], fn($value) => !empty($value)));
    }

    public function getColeaderCount(): int
    {
        return count(array_filter($this->getMembers()["coleader"], fn($value) => !empty($value)));
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

        if($guildmaster->hasPermission(Constants::GUILD_MAX_MEMBERS_TWO_HUNDRED)) $this->setMaxMembersAllowed(200);
        else if($guildmaster->hasPermission(Constants::GUILD_MAX_MEMBERS_HUNDRED)) $this->setMaxMembersAllowed(100);
        else $this->setMaxMembersAllowed(50);

        if($oldMax !== $this->maxMembersAllowed)
            MySQLDatabase::update("GuildsInfo", "MaxAllowedMembers", $this->getName(), $this->maxMembersAllowed);
    }

    public function addMember(Session $member): void
    {
        $name = $member->getName();

        $member->setGuildRole(GuildManager::getGuildRoleByName("member"));
        $member->updateGuild($this);
        $this->members["member"][] = $name;
    }

    public function removeMember(string $member, string $cause): void
    {
        MySQLDatabase::update('Guilds', 'GuildName', $member, null);
        MySQLDatabase::update('Guilds', 'GuildRole', $member, null);

        //Obtain the member's guild rank
        if($this->isMember($member)) $rank = "member";
        else if($this->isOfficer($member)) $rank = "officer";
        else $rank = "coleader";

        $index = array_search($member, $this->members[$rank]);
        $this->members[$rank][$index] = null;
        if(array_key_exists($member, SessionManager::getSessions()))
        {
            $session = SessionManager::getSessionByName($member);
            $session->removeFromGuild($cause);
        }
    }

    protected function changeGuildRank(string $current, string $result, string $target): void
    {
        MySQLDatabase::update("Guilds", "GuildRole", $target, $result);
        $this->members[$current][array_search($target, $this->members[$current])] = null;
        $index = array_search(null, $this->members[$result]);
        $index = is_bool($index) ? null : $index;
        $this->members[$result][$index] = $target;
    }

    public function promote(string $target): string
    {
        if($this->isMember($target))
        {
            $result = "officer";
            $current = "member";
        }
        else
        {
            $result = "coleader";
            $current = "officer";
        }

        $this->changeGuildRank($current, $result, $target);

        if(array_key_exists($target, SessionManager::getSessions()))
        {
            $session = SessionManager::getSessionByName($target);
            $session->setGuildRole(GuildManager::getGuildRoleByName($result));
            $session->sendMessage("[Guild] You were promoted to ".ucfirst($result)."!");
        }

        return ucfirst($result);
    }

    public function demote(string $target): string
    {
        if($this->isColeader($target))
        {
            $result = "officer";
            $current = "coleader";
        }
        else
        {
            $result = "member";
            $current = "officer";
        }

        $this->changeGuildRank($current, $result, $target);

        if(array_key_exists($target, SessionManager::getSessions()))
        {
            $session = SessionManager::getSessionByName($target);
            $session->setGuildRole(GuildManager::getGuildRoleByName($result));
            $session->sendMessage("[Guild] You were demoted to ".ucfirst($result)."!");
        }

        return ucfirst($result);
    }

    public function transferOwnership(string $target): string
    {
        return;
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

    public function isTheGuildmaster(string $player): bool
    {
        return !strcmp($player, $this->members["guildmaster"]);
    }
}