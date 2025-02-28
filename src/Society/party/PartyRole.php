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

    public function getRoleName(): string
    {
        return $this->title;
    }

    public function getRolePermissions(): array
    {
        return $this->permissions;
    }
}