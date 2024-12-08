<?php

namespace Society\commands\friends;

use http\Exception\RuntimeException;
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

        foreach ($friends as $friend) {
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
            $applicant = SessionManager::getSessionByName($sender->getName());
            $invitation = new FriendInvitation($applicant, $receiver);
            $applicant->sendFriendInvitation($invitation);
            $receiver->receiveFriendInvitation($invitation);
        }
        catch (Exception)
        {
            $sender->sendMessage("Player $receiverName is not online.");
        }
    }

    public static function accept(CommandSender $sender, string $name): void #Accepts a request
    {
        $receiver = SessionManager::getSessionByName($sender->getName());
        try
        {
            $invitation = $receiver->getFriendInvitesReceived()[$name];
            if (is_null($invitation)) throw new RuntimeException("Unknown player");
            $applicant = $invitation->getSender();

            $applicant->addFriend($receiver, 'sent');
            $receiver->addFriend($applicant, 'received');
        }
        catch (Exception)
        {
            $receiver->sendMessage("You don't have a friend request from $name");
            return;
        }
    }

    public static function decline(CommandSender $sender, string $name): void #Declines a request (sent by another player)
    {
        $receiver = SessionManager::getSessionByName($sender->getName());
        try
        {
            $invitation = $receiver->getFriendInvitesReceived()[$name];
            $applicant = $invitation->getReceiver();

            $applicant->removeFriendInvitation($name, 'decline', 'sent');
            $receiver->removeFriendInvitation($sender->getName(), 'decline', 'received');
        }
        catch (Exception)
        {
            $receiver->sendMessage("You don't have a friend request from $name");
            return;
        }
    }

    public static function abort(CommandSender $sender, string $name): void #Deletes a REQUEST (sent by the player itself)
    {
        $applicant = SessionManager::getSessionByName($sender->getName());
        try
        {
            $invitation = $applicant->getFriendInvitesReceived()[$name];
            $receiver = $invitation->getReceiver();

            $applicant->removeFriendInvitation($name, 'abort', 'sent');
            $receiver->removeFriendInvitation($sender->getName(), 'abort', 'received');
        }
        catch (Exception)
        {
            $applicant->sendMessage("You haven't sent a friend request to $name");
            return;
        }
    }

    public static function remove(CommandSender $sender, string $name): void #Removes a FRIEND
    {
        $applicant = SessionManager::getSessionByName($sender->getName());
    }
}