<?php

namespace Society\commands\friends;

use pocketmine\command\CommandSender;

use Society\commands\friends\utils\FriendInvitation;
use Society\session\SessionManager;

use Exception;

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

    public static function add(CommandSender $sender, string $receiverName): void #Requests
    {
        try
        {
            $receiver = SessionManager::getSessionByName($receiverName);
        }
        catch (Exception)
        {
            $sender->sendMessage("Player $args[1] is not online.");
        }
        $applicant = SessionManager::getSessionByName($sender->getName());
        $invitation = new FriendInvitation($applicant, $receiver);
        $applicant->sendFriendInvitation($invitation);
        $receiver->receiveFriendInvitation($invitation);
    }

    public static function accept(CommandSender $sender, string $name): void #Accepts a request
    {
        $applicant = SessionManager::getSessionByName($sender->getName());
        try
        {
            $invitation = $applicant->getFriendInvitesReceived()[$name];
        }
        catch (Exception)
        {
            $applicant->sendMessage("You don't have a friend request from $name");
            return;
        }
        $receiver = $invitation->getReceiver();

        $applicant->addFriend($receiver, 'sent');
        $receiver->addFriend($applicant, 'received');
    }

    public static function decline(CommandSender $sender, string $name): void #Declines a request (sent by another player)
    {

    }

    public static function delete(CommandSender $sender): void #Deletes a REQUEST (sent by the player itself)
    {

    }

    public static function remove(CommandSender $sender): void #Removes a FRIEND
    {

    }
}