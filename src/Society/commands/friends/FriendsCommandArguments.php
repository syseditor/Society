<?php

namespace Society\commands\friends;

use pocketmine\command\CommandSender;

use Society\session\SessionManager;

class FriendsCommandArguments
{

    public static function list(CommandSender $sender): void
    {
        $session = SessionManager::getSessionByName($sender->getName());
        $friends = $session->getFriendList();
        $final = "";
        $sum = 0;

        foreach ($friends as $friend)
        {
            if (is_null($friend)) continue;
            $final .= ';' . $friend;
            ++$sum;
        }
        if (empty($final)) $final = "None";
        $sender->sendMessage("You have $sum/10 friends: $final");
    }

    public static function add(CommandSender $sender): void
    {

    }

    public static function remove(CommandSender $sender): void
    {

    }

    public static function accept(CommandSender $sender): void
    {

    }

    public static function decline(CommandSender $sender): void
    {

    }
}