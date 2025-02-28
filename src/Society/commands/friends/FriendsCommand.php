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

namespace Society\commands\friends;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use Society\Society;

class FriendsCommand extends Command
{
    protected Society $plugin;

    public function __construct()
    {
        parent::__construct("friends", "Friends system!", "Usage: /[friends, friend,f] <help/list> [args]", ["friend", "f"]);
        $this->setPermission("society.friends");
        $this->plugin = Society::getInstance();
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        $usage = $this->usageMessage;
        if (empty($args)) {$sender->sendMessage($this->usageMessage); return;}
        switch ($args[0])
        {
            case 'help':
                $sender->sendMessage($usage);
                break;
            case 'list':
                FriendsCommandArguments::list($sender);
                break;
            case 'add':
                if (!isset($args[1])) {$sender->sendMessage("You need to specify a player"); return;}
                FriendsCommandArguments::add($sender, $args[1]);
                break;
            case 'accept':
                if (!isset($args[1])) {$sender->sendMessage("You need to specify a player who sent a friend request"); return;}
                FriendsCommandArguments::accept($sender, $args[1]);
                break;
            case 'decline':
                if (!isset($args[1])) {$sender->sendMessage("You need to specify a player who sent a friend request"); return;}
                FriendsCommandArguments::decline($sender, $args[1]);
                break;
            case 'delete':
                if (!isset($args[1])) {$sender->sendMessage("You need to specify a player, to which you sent a friend request"); return;}
                FriendsCommandArguments::abort($sender, $args[1]);
                break;
            case 'remove':
                if (!isset($args[1])) {$sender->sendMessage("You need to specify a friend"); return;}
                FriendsCommandArguments::remove($sender, $args[1]);
                break;
            case 'requests':
                FriendsCommandArguments::requests($sender);
                break;
            default: {$sender->sendMessage("Invalid usage. Usage: $usage"); return;}
        }
    }
}