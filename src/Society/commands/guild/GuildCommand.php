<?php

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
            default: $sender->sendMessage($this->getUsage());
        }
    }
}