<?php

namespace Society\guild;

class GuildRole
{
    private string $roleName;

    public function __construct(string $roleName)
    {
        $this->roleName = $roleName;
    }

    public function getRoleName(): string
    {
        return $this->roleName;
    }
}