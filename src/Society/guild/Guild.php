<?php

namespace Society\guild;

use Society\session\Session;
use Society\session\SessionManager;

class Guild
{
    private string $name;
    private string $guildmaster; //required
    private int $level;
    private int $exp;
    private int $memberCount;
    private int $maxMembersAllowed;
    private array $members;

    public function __construct(string $name, string $guildmaster, array $members, int $level = 1, int $exp = 0, int $maxAllowedMembers = 50)
    {
        $this->name = $name;
        $this->level = $level;
        $this->exp = $exp;
        $this->maxMembersAllowed = $maxAllowedMembers;
        $this->members = $members;
        $this->memberCount = count($members);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGuildmaster(): string
    {
        return $this->guildmaster;
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

    public function getMemberCount(): int
    {
        return $this->memberCount;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function setGuildmaster(string $name): void //for ownership transfers (mostly)
    {
        $this->guildmaster = $name;
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
}