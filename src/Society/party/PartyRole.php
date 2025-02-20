<?php

namespace Society\party;

class PartyRole
{
    private string $title;
    private array $permissions;

    public function __construct(string $title)
    {
        $this->title = $title;
        $this->assignPermissions();
    }

    protected function assignPermissions(): void
    {
        switch($this->title)
        {
            case "Leader" || "leader":
                $this->permissions = PartyManager::$rolePermissions["leader"];
                break;
            case "Officer" || "officer":
                $this->permissions = PartyManager::$rolePermissions["officer"];
                break;
            case "Member" || "member":
                $this->permissions = PartyManager::$rolePermissions["member"];
                break;
            default: break;
        }
    }

    public function getTitleName(): string
    {
        return $this->title;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}