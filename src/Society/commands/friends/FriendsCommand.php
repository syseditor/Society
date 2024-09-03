<?php

namespace Society\commands\friends;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use Society\session\SessionManager;
use Society\Society;

use Exception;

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
                FriendsCommandArguments::delete($sender, $args[1]);
                break;
            case 'remove':
                if (!isset($args[1])) {$sender->sendMessage("You need to specify a friend"); return;}
                FriendsCommandArguments::remove($sender, $args[1]);
                break;
            default: {$sender->sendMessage("Invalid usage. Usage: $usage"); return;}
        }
    }
}