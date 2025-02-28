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

namespace Society\commands\party;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use Society\session\SessionManager;
use Society\Society;

class PartyCommand extends Command
{
    protected Society $plugin;

    public function __construct()
    {
        parent::__construct("party", "A command to manage/create parties!", "Usage: /party <arguments>", ["p"]);
        $this->setPermission("society.party");
        $this->plugin = Society::getInstance();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $usage = $this->getUsage();
        $sender = SessionManager::getSessionByName($sender->getName());
        if(empty($args[0])) { $sender->sendMessage($usage); return; }
        switch($args[0])
        {
            case "info":
                PartyCommandArguments::info($sender);
                break;
            case "invites":
                PartyCommandArguments::invites($sender);
                break;
            case "create":
                PartyCommandArguments::create($sender);
                break;
            case "disband":
                PartyCommandArguments::disband($sender);
                break;
            case "invite":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a player."); return; }
                PartyCommandArguments::invite($sender, $args[1]);
                break;
            case "accept":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a party."); return; }
                PartyCommandArguments::accept($sender, $args[1]);
                break;
            case "deny":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a party."); return; }
                PartyCommandArguments::deny($sender, $args[1]);
                break;
            case "transfer":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a player."); return; }
                PartyCommandArguments::transfer($sender, $args[1]);
                break;
            case "kick":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a player."); return; }
                PartyCommandArguments::kick($sender, $args[1]);
                break;
            case "promote":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a player."); return; }
                PartyCommandArguments::promote($sender, $args[1]);
                break;
            case "demote":
                if(!isset($args[1])) { $sender->sendMessage("You need to specify a player."); return; }
                PartyCommandArguments::demote($sender, $args[1]);
                break;
            case "chat":
                PartyCommandArguments::chat($sender);
                break;
            case "leave":
                PartyCommandArguments::leave($sender);
                break;
            default: $sender->sendMessage($usage);
        }
    }
}