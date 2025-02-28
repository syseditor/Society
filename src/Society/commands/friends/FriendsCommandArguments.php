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

use http\Exception\RuntimeException;
use pocketmine\command\CommandSender;

use Society\commands\friends\utils\FriendInvitation;
use Society\session\Session;
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

    public static function requests(CommandSender $sender): void
    {
        $session = SessionManager::getSessionByName($sender->getName());
        $reqs = $session->getFriendInvitesReceived();
        $final = "";
        $index = 1;

        foreach ($reqs as $invitation)
        {
            if($invitation instanceof FriendInvitation)
            {
                $inviter = $invitation->getSender();
                $final = $final . "$index) " . $inviter->getPlayer()->getName() . "\n";
            }
        }
        if(empty($final)) $final = "You have no requests";
        else
        {
            $text = "You have $index requests:\n\n";
            $final = $text . $final;
        }

        $session->sendMessage($final);
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
            if(!$applicant instanceof Session) throw new Exception();
            if(!strcmp($receiver->getPlayer()->getName(), $applicant->getPlayer->getName()))
            {
                $receiver->sendMessage("You can't invite yourself!");
                return;
            }

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