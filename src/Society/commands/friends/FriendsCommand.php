<?php

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

    /**
     * @inheritDoc
     */
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
                break;
            default:
                $sender->sendMessage("Invalid usage. Usage: $usage");
        }
    }
}