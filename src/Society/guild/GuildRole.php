<?php

namespace Society\guild;

class GuildRole
{
    private string $roleName;
    private array $rolePermissions;

    public function __construct(string $roleName, array $rolePermissions)
    {
        $this->roleName = $roleName;
    }

    public function getRoleName(): string
    {
        return $this->roleName;
    }
}