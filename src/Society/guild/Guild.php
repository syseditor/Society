<?php

namespace Society\guild;

use Society\database\mysql\MySQLDatabase;
use Society\session\Session;
use Society\session\SessionManager;

class Guild
{
    private string $name;
    private int $level;
    private int $exp;
    private int $maxMembersAllowed;
    private array $members;

    public function __construct(string $name, array $members, int $level = 1, int $exp = 0, int $maxAllowedMembers = 50)
    {
        $this->name = $name;
        $this->level = $level;
        $this->exp = $exp;
        $this->maxMembersAllowed = $maxAllowedMembers;
        $this->members = $members;
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

    public function refresh(): void
    {
        $this->updateMaxMembersAllowed();
    }

    protected function updateMaxMembersAllowed(): void //guildmaster must be online => only called by themselves
    {
        $guildmaster = SessionManager::getSessionByName($this->members["guildmaster"]);

        if($guildmaster->hasPermission("society.guild.twohundred")) $this->setMaxMembersAllowed(200);
        else if($guildmaster->hasPermission("society.guild.hundred")) $this->setMaxMembersAllowed(100);
        else $this->setMaxMembersAllowed(50);

        MySQLDatabase::update("GuildsInfo", "MaxAllowedMembers", $this->getName(), $this->maxMembersAllowed);
    }
}