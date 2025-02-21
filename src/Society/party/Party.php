<?php

namespace Society\party;

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
        if($this->leader->getPlayer()->hasPermission(Constants::PARTY_MAX_MEMBERS_TEN)) $this->maxPartyMembersAllowed = 10;
        else if($this->leader->getPlayer()->hasPermission(Constants::PARTY_MAX_MEMBERS_TWENTY)) $this->maxPartyMembersAllowed = 20;
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
            return true;
        }
        return false;
    }

    public function promote(Session $member): bool
    {
        if(!is_null($this->officer))
        {
            if(strcmp($this->officer->getName(), $member->getName()))
            {
                $this->demote($this->officer);
                return $this->promote($member);
            }
            return false;
        }
        else
        {
            $role = new PartyRole("officer");
            $member->setPartyRole($role);
            $this->officer = $member;
            return true;
        }
    }

    public function demote(Session $member): void
    {
        if(!is_null($this->officer))
        {
            $role = new PartyRole("member");
            $member->setPartyRole($role);
            $this->officer = null;
        }
    }

    public function kick(Session $member): bool
    {
        return false;
    }

    public function disband(): bool
    {
        return false;
    }
}