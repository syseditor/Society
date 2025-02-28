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

namespace Society\commands\guild;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use Society\session\SessionManager;

class GuildCommand extends Command
{
    public function __construct()
    {
        parent::__construct("guild", "Manage your guild!", "Usage: /guild <args>", ["g"]);
        $this->setPermission("society.guild");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $sender = SessionManager::getSessionByName($sender->getName());
        if(!isset($args[0])) { $sender->sendMessage($this->getUsage()); return; }
        switch($args[0])
        {
            case "info":
                GuildCommandArguments::info($sender);
                break;
            case "create":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a name for your Guild!"); return; }
                GuildCommandArguments::create($sender, $args[1]);
                break;
            case "disband":
                GuildCommandArguments::disband($sender);
                break;
            case "cancel":
                GuildCommandArguments::cancel($sender);
                break;
            case "refresh":
                GuildCommandArguments::refresh($sender);
                break;
            case "chat":
                GuildCommandArguments::chat($sender);
                break;
            case "promote":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a target!"); return; }
                GuildCommandArguments::promote($sender, $args[1]);
                break;
            case "demote":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a target"); return; }
                GuildCommandArguments::demote($sender, $args[1]);
                break;
            case "invite":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a target!"); return; }
                GuildCommandArguments::invite($sender, $args[1]);
                break;
            case "kick":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a target!"); return; }
                GuildCommandArguments::kick($sender, $args[1]);
                break;
            case "accept":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a guild!"); return; }
                GuildCommandArguments::accept($sender, $args[1]);
                break;
            case "decline":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a guild!"); return; }
                GuildCommandArguments::decline($sender, $args[1]);
                break;
            case "transfer":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a target!"); return; }
                GuildCommandArguments::transfer($sender, $args[1]);
                break;
            default: $sender->sendMessage($this->getUsage());
        }
    }
}