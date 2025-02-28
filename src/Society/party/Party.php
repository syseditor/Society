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

use Society\guild\GuildManager;
use Society\session\Session;
use Society\utils\Constants;

class Party
{
    private Session $leader;
    private ?Session $officer;
    private array $members;
    private int $memberCount;
    private int $maxPartyMembersAllowed;

    public function __construct(Session $leader)
    {
        $this->leader = $leader;
        $this->members = array($leader);
        $this->officer = null;
        $this->memberCount = 1; //the leader
        $this->calculateMaxMembers();
    }

    protected function calculateMaxMembers(): void
    {
        if($this->leader->getPlayer()->hasPermission(Constants::PARTY_MAX_MEMBERS_TWENTY)) $this->maxPartyMembersAllowed = 20;
        else if($this->leader->getPlayer()->hasPermission(Constants::PARTY_MAX_MEMBERS_TEN)) $this->maxPartyMembersAllowed = 10;
        else $this->maxPartyMembersAllowed = 5;
    }

    public function getLeader(): Session
    {
        return $this->leader;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function getMemberCount(): int
    {
        return $this->memberCount;
    }

    public function getMaxMembersAllowed(): int
    {
        return $this->maxPartyMembersAllowed;
    }

    public function getOfficer(): ?Session
    {
        return $this->officer;
    }

    public function addMember(Session $member): bool
    {
        if($this->memberCount < $this->maxPartyMembersAllowed)
        {
            if(in_array(null, $this->members)) $this->members[array_search(null, $this->members)] = $member;
            else $this->members[] = $member;
            $member->addToParty($this);
            $member->setPartyRole(PartyManager::$roles["member"]);
            ++$this->memberCount;
            return true;
        }
        return false;
    }

    public function removeMember(Session $member): bool
    {
        if(in_array($member, $this->members))
        {
            $this->members[array_search($member, $this->members)] = null;
            --$this->memberCount;
            $member->setCurrentChat(Constants::CHAT_GLOBAL);
            return true;
        }
        return false;
    }

    public function broadcastMessage(string $message): void
    {
        foreach($this->members as $member) if(!is_null($member)) $member->sendMessage($message);
    }

    public function promote(Session $member): bool
    {
        if(!is_null($this->officer))
        {
            if(strcmp($this->officer->getName(), $member->getName()))
            {
                $this->demote($this->officer);
                $this->officer->sendMessage("[Party] You've been demoted to Member!");
                return $this->promote($member);
            }
            return false;
        }
        else
        {
            $member->setPartyRole(PartyManager::$roles["member"]);
            $this->officer = $member;
            return true;
        }
    }

    public function demote(Session $member): void
    {
        if(!is_null($this->officer))
        {
            $member->setPartyRole(PartyManager::$roles["member"]);
            $this->officer = null;
        }
    }

    public function transferOwnership(Session|string $member): void
    {
        $this->leader->setPartyRole(PartyManager::$roles["member"]);
        $this->leader = $member;
        $member->setPartyRole(PartyManager::$roles["leader"]);
    }

    public function kick(Session $member): bool
    {
        if(in_array($member, $this->members))
        {
            if(strcmp($member->getPartyRole()->getRoleName(), "officer")) $this->officer = null;
            $this->members[array_search($member, $this->members)] = null;
            --$this->memberCount;
            $member->removeFromParty("[Party] You were kicked from " . $this->leader->getName() . "'s party.");
            $member->setCurrentChat(Constants::CHAT_GLOBAL);
            return true;
        }
        return false;
    }

    public function disband(): bool
    {
        foreach($this->members as $member) $member->removeFromParty("disband");
        PartyManager::removeParty($this);
        return true; //always true for now
    }
}