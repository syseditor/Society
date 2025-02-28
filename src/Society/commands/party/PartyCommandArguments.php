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

use Society\party\PartyInvitation;
use Society\party\PartyManager;
use Society\session\Session;
use Society\party\Party;
use Society\session\SessionManager;
use Society\utils\Constants;

class PartyCommandArguments
{
    public static function info(Session $sender): void
    {
        if($sender->checkAvailability("party"))
        {
            $final = "Current party's information:\n\n";
            $members = array();

            $party = $sender->getParty();
            $leader = $party->getLeader()->getName();
            $officer = is_null($party->getOfficer()) ? "No officer assigned" : $party->getOfficer()->getName();
            $membercount = $party->getMemberCount();
            $maxmembers = $party->getMaxMembersAllowed();
            $final .= "Leader: " . $leader . "\n";
            $final .= "Officer: " . $officer . "\n";
            foreach($party->getMembers() as $member) $members[] = $member->getName();
            $final .= "Members: " . join(", ", $members) . "\n";
            $final .= "Total members: $membercount/$maxmembers";

            $sender->sendMessage($final);
        }
        else $sender->sendMessage("You are not in a party.");
    }

    public static function invites(Session $sender): void
    {
        $total = 0;
        $final = "You have $total invites";

        foreach($sender->getPartyInvites() as $partyName => $invitation)
        {
            if(is_null($invitation)) continue;
            $total += 1;
            if($total === 1) $final .= ":\n\n" . "$total) " . $partyName;
            else $final .= "\n" . "$total) " . $partyName;
        }

        $sender->sendMessage($final);
    }

    public static function create(Session $sender): void
    {
        if(!$sender->checkAvailability("party"))
        {
            $party = new Party($sender);
            $sender->setPartyRole(PartyManager::$roles["leader"]);
            $sender->addToParty($party);
            PartyManager::registerParty($party);

            $sender->sendMessage("[Party] You successfully created a party!");
        }
        else $sender->sendMessage("[Party] You are already in a party.");
    }

    public static function disband(Session $sender): void
    {
        if($sender->checkAvailability("party"))
        {
            if($sender->hasPartyPermission("canDisband"))
            {
                if($sender->getParty()->disband()) $sender->sendMessage("[Party] You successfully disbanded your party!");
                else $sender->sendMessage("[Party] Disband failed. Contact the administrators for a possible bug.");
            }
            else $sender->sendMessage("[Party] You cannot disband this party. Try \"/party leave\" if you would like to leave it.");
        }
        else $sender->sendMessage("You have not created a party to disband.");
    }

    public static function kick(Session $sender, string $target): void
    {
        if($sender->checkAvailability("party"))
        {
            if($sender->hasPartyPermission("canKick"))
            {
                if(!strcmp($target, $sender->getName()))
                {
                    $sender->sendMessage("[Party] You cannot kick yourself! Try \"/party leave\" to leave your party.");
                    return;
                }

                if(array_key_exists($target, SessionManager::getSessions())) $target = SessionManager::getSessionByName($target);
                else
                {
                    $sender->sendMessage("This player is not online.");
                    return;
                }

                $party = $sender->getParty();
                if($party->kick($target))
                {
                    $sender->sendMessage("[Party] You successfully kicked " . $target->getName() . " out of your party.");
                    if(!strcmp($sender->getPartyRole()->getRoleName(), "leader")) $party->getLeader()->sendMessage("[Party] " . $sender->getName() . "kicked " . $target->getName() . " out of your party.");
                    $party->broadcastMessage("[Party] " . $target->getName() . " was kicked out of your party.");
                }
                else $sender->sendMessage("[Party] Target is not in your party.");
            }
            else $sender->sendMessage("[Party] You are not allowed to kick party members.");
        }
        else $sender->sendMessage("You are not in a party.");
    }

    public static function promote(Session $sender, string $target): void
    {
        if($sender->checkAvailability("party"))
        {
            if($sender->hasPartyPermission("canDisband")) //leader only
            {
                if(!strcmp($sender->getName(), $target))
                {
                    $sender->sendMessage("[Party] You cannot promote yourself!");
                    return;
                }

                if(array_key_exists($target, SessionManager::getSessions())) $target = SessionManager::getSessionByName($target);
                else
                {
                    $sender->sendMessage("This player is not online.");
                    return;
                }

                $party = $sender->getParty();
                if($party->promote($target))
                {
                    $sender->sendMessage("[Party] You successfully promoted " . $target->getName() . ".");
                    $target->sendMessage("[Party] You were promoted to Officer!");
                }
                else $sender->sendMessage("[Party] The officer cannot be further promoted. Transfer the ownership using \"/party transfer\".");
            }
            else $sender->sendMessage("[Party] You do not have permission to promote members.");
        }
        else $sender->sendMessage("[Party] You are not in a party.");
    }

    public static function demote(Session $sender, string $target): void
    {
        if($sender->checkAvailability("party"))
        {
            if($sender->hasPartyPermission("canDisband"))
            {
                if(!strcmp($sender->getName(), $target))
                {
                    $sender->sendMessage("[Party] You cannot demote yourself.");
                    return;
                }

                if(array_key_exists($target, SessionManager::getSessions())) $target = SessionManager::getSessionByName($target);
                else
                {
                    $sender->sendMessage("This player is not online.");
                    return;
                }

                $party = $sender->getParty();
                $party->demote($target);
                $sender->sendMessage("[Party] You successfully demoted " . $target->getName() . ".");
                $target->sendMessage("[Party] You were demoted to Member!");
            }
            else $sender->sendMessage("[Party] You cannot demote members.");
        }
        else $sender->sendMessage("[Party] You are not in a party.");
    }

    public static function invite(Session $sender, string $target): void
    {
        if($sender->checkAvailability("party"))
        {
            if($sender->hasPartyPermission("canInvite"))
            {
                if(!strcmp($sender->getName(), $target))
                {
                    $sender->sendMessage("[Party] You cannot invite yourself!");
                    return;
                }

                if(array_key_exists($target, SessionManager::getSessions())) $target = SessionManager::getSessionByName($target);
                else
                {
                    $sender->sendMessage("This player is not online.");
                    return;
                }

                $party = $sender->getParty();
                $invitation = new PartyInvitation($sender, $target, $party);
                $target->receivePartyInvitation($invitation);
                $sender->sendMessage("[Party] Invited " . $target->getName() . " to the party!");
            }
            else $sender->sendMessage("[Party] You are not allowed to invite new members.");
        }
        else $sender->sendMessage("[Party] You are not in a party.");
    }

    public static function accept(Session $sender, string $partyName): void
    {
        if(!$sender->checkAvailability("party"))
        {
            if(array_key_exists($partyName, $sender->getPartyInvites()))
            {
                if(array_key_exists($partyName, PartyManager::getParties()))
                {
                    $party = PartyManager::getPartyByLeader($partyName);
                    if($party->addMember($sender))
                    {
                        $sender->sendMessage("[Party] Welcome to " . $partyName . "'s party!");
                        $party->broadcastMessage("[Party] " . $sender->getName() . " joined the party!");

                        $sender->removePartyInvitation($partyName);
                    }
                    else $sender->sendMessage("This party is currently full.");
                }
                else
                {
                    $sender->sendMessage("This party no longer exists.");
                    $sender->removePartyInvitation($partyName);
                }
            }
            else $sender->sendMessage("You were not invited by this party.");
        }
        else $sender->sendMessage("[Party] You are already in a party.");
    }

    public static function deny(Session $sender, string $partyName): void
    {
        if(array_key_exists($partyName, $sender->getPartyInvites()))
        {
            if(array_key_exists($partyName, PartyManager::getParties()))
            {
                $invitation = $sender->getPartyInvites()[$partyName];
                $inviter = $invitation->getInviter();
                $sender->sendMessage("You rejected " . $inviter->getName() . "'s invitation.");
                $inviter->sendMessage($sender->getName() . " rejected your party invitation.");
            }
            else
            {
                $sender->sendMessage("This party no longer exists.");
                $sender->removePartyInvitation($partyName);
            }
        }
        else $sender->sendMessage("You were not invited by this party.");
    }

    public static function chat(Session $sender): void
    {
        if($sender->checkAvailability("party"))
        {
            if($sender->getCurrentChat() == Constants::CHAT_PARTY)
            {
                $sender->setCurrentChat(Constants::CHAT_GLOBAL);
                $sender->sendMessage("[Party] You switched to Global chat.");
            }
            else
            {
                $sender->setCurrentChat(Constants::CHAT_PARTY);
                $sender->sendMessage("[Party] You switched to Party chat.");
            }
        }
        else $sender->sendMessage("[Party] You are not in a party.");
    }

    public static function leave(Session $sender): void
    {
        if($sender->checkAvailability("party"))
        {
            $party = $sender->getParty();
            if(!strcmp($sender->getPartyRole()->getRoleName(), "leader")) $party->transferOwnership("r");
            $party->removeMember($sender);
            $sender->removeFromParty("[Party] You left your party.");
        }
        else $sender->sendMessage("You are not in a party.");
    }

    public static function transfer(Session $sender, string $target): void
    {
        if($sender->checkAvailability("party"))
        {
            if($sender->hasPartyPermission("canDisband"))
            {
                if(!strcmp($sender->getName(), $target))
                {
                    $sender->sendMessage("[Party] You cannot transfer the ownership to yourself!");
                    return;
                }

                if(array_key_exists($target, SessionManager::getSessions())) $target = SessionManager::getSessionByName($target);
                else
                {
                    $sender->sendMessage("This player is not online.");
                    return;
                }

                $party = $sender->getParty();
                $party->transferOwnership($target);
                $sender->sendMessage("[Party] The party's ownership was transferred to " . $target->getName() . ".");
                $target->sendMessage("[Party] The leader transferred the party's ownership to you!");
            }
            else $sender->sendMessage("[Party] You cannot alter the party's ownership.");
        }
        else $sender->sendMessage("You are not in a party.");
    }
}