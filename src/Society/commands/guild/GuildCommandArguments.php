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

namespace Society\commands\guild;

use Society\database\mysql\MySQLDatabase;
use Society\session\Session;
use Society\session\SessionManager;
use Society\guild\Guild;
use Society\guild\GuildManager;
use Society\utils\Constants;

class GuildCommandArguments
{
    public static function info(Session $sender): void
    {
        if($sender->checkAvailability("guild"))
        {
            $guild = $sender->getGuild();
            $final = "Current Guild Information\n--------------------\n";

            $name = $guild->getName();
            $guildMembers = $guild->getMembers();
            $maxmembers = $guild->getMaxTotalMembersAllowed();
            $membercount = $guild->getTotalMemberCount();

            $guildmaster = "Guildmaster: " . $guildMembers["guildmaster"] . "\n";
            $coleaders = "";
            $officers = "";
            $members = "";

            foreach($guildMembers as $rank => $playerArray)
            {
                if(!strcmp($rank, "coleader")) $coleaders = "Coleaders: " . join(", ", $playerArray) . "\n";
                else if(!strcmp($rank, "officer")) $officers = "Officers: " . join(", ", $playerArray) . "\n";
                else if(!strcmp($rank, "member")) $members = "Members: " . join(", ", $playerArray) . "\n";
            }

            $final .= "Guild: $name\n" . $guildmaster . $coleaders . $officers . $members . "Total Guild members: $membercount/$maxmembers";
            $sender->sendMessage($final);
        }
        else $sender->sendMessage("You are not in a guild.");
    }

    public static function create(Session $sender, string $name): void
    {
        if(!$sender->checkAvailability("guild"))
        {
            if($sender->canCreateGuild())
            {
                if(!GuildManager::guildExists($name))
                {
                    $members = array(
                        "guildmaster" => $sender->getName(),
                        "coleader" => [],
                        "officer" => [],
                        "member" => []
                    );

                    $guild = new Guild($name, $members);
                    $sender->setGuildRole(GuildManager::getGuildRoleByName("guildmaster"));
                    $sender->updateGuild($guild);
                    GuildManager::registerGuild($guild);
                    MySQLDatabase::registerGuild($guild);
                    $guild->refresh();

                    $sender->sendMessage("[Guild] The Guild $name was successfully created! Welcome!");
                }
                else $sender->sendMessage("A guild with the name $name (case-insensitive check) already exists!");
            }
            else $sender->sendMessage("You are not allowed to create a guild.");
        }
        else $sender->sendMessage("[Guild] You are already in a guild.");
    }

    public static function disband(Session $sender): void
    {
        if($sender->checkAvailability("guild"))
        {
            if($sender->hasGuildPermission("guildAdmin"))
            {
                $guild = $sender->getGuild();
                if($guild->wasGivenPermissionToDisband())
                {
                    $name = $guild->getName();
                    $sender->sendMessage("[Guild] Disbanding Guild $name...");
                    $guild->disband();
                    $sender->sendMessage("[Guild] Successfully disbanded Guild $name.");
                }
                else
                {
                    $sender->sendMessage("[Guild] Are you sure you want to disband this guild? Type \"/guild disband\" to confirm or \"/guild cancel\" to abort.");
                    $guild->setPermissionToDisband(true);
                }
            }
            else $sender->sendMessage("[Guild] You do not have permission to disband your guild.");
        }
        else $sender->sendMessage("You are not in a guild.");
    }

    public static function cancel(Session $sender): void
    {
        if($sender->checkAvailability("guild"))
        {
            if($sender->hasGuildPermission("guildAdmin"))
            {
                $sender->getGuild()->setPermissionToDisband(false);
                $sender->sendMessage("[Guild] You canceled any attempts to disband the guild.");
            }
            else $sender->sendMessage("[Guild] You do not have permission to execute this command.");
        }
        else $sender->sendMessage("You are not in a guild.");
    }

    public static function refresh(Session $sender): void
    {
        if($sender->checkAvailability("guild"))
        {
            if($sender->hasGuildPermission("guildAdmin"))
            {
                $sender->getGuild()->refresh();
                $sender->sendMessage("[Guild] Successfully refreshed your Guild status.");
            }
            else $sender->sendMessage("[Guild] You do not have permission to execute this command.");
        }
        else $sender->sendMessage("You are not in a guild.");
    } //NOT so important rn

    public static function chat(Session $sender): void
    {
        if($sender->checkAvailability("guild"))
        {
            if($sender->getCurrentChat() == Constants::CHAT_GLOBAL || $sender->getCurrentChat() == Constants::CHAT_PARTY)
            {
                $sender->setCurrentChat(Constants::CHAT_GUILD);
                $sender->sendMessage("[Guild] You switched to Guild chat.");
            }
            else
            {
                $sender->setCurrentChat(Constants::CHAT_GUILD);
                $sender->sendMessage("[Guild] You switched to Global chat.");
            }
        }
        else $sender->sendMessage("You are not in a guild.");
    }

    public static function promote(Session $sender, string $target): void
    {
        if($sender->checkAvailability("guild"))
        {
            if($sender->hasGuildPermission("canPromote"))
            {
                $guild = $sender->getGuild();
                if($guild->isGuildMember($target))
                {
                    if(($guild->isMember($target) && $guild->getOfficerCount() < $guild->getMaxOfficersAllowed())
                        || ($guild->isOfficer($target) && $guild->getColeaderCount() < $guild->getMaxColeadersAllowed()))
                    {
                        $result = $guild->promote($target);
                        $sender->sendMessage("[Guild] Successfully promoted $target to $result.");
                    }
                    else $sender->sendMessage("[Guild] $target cannot be promoted.");
                }
                else $sender->sendMessage("[Guild] $target is not part of the guild.");
            }
            else $sender->sendMessage("[Guild] You do not have permission to execute this command.");
        }
        else $sender->sendMessage("You are not in a guild.");
    }

    public static function demote(Session $sender, string $target): void
    {
        if($sender->checkAvailability("guild"))
        {
            if($sender->hasGuildPermission("canPromote"))
            {
                $guild = $sender->getGuild();
                if($guild->isGuildMember($target))
                {
                    if(($guild->isColeader($target) && $guild->getOfficerCount() < $guild->getMaxOfficersAllowed()) || $guild->isOfficer($target))
                    {
                        $result = $guild->demote($target);
                        $sender->sendMessage("[Guild] Successfully demoted $target to $result.");
                    }
                    else $sender->sendMessage("[Guild] $target cannot be demoted.");
                }
                else $sender->sendMessage("[Guild] $target is not part of the guild.");
            }
            else $sender->sendMessage("[Guild] You do not have permission to execute this command.");
        }
        else $sender->sendMessage("You are not in a guild.");
    }

    public static function kick(Session $sender, string $target): void
    {
        if($sender->checkAvailability("guild"))
        {
            if($sender->hasGuildPermission("canKick"))
            {
                $guild = $sender->getGuild();
                if($guild->isGuildMember($target))
                {
                    $guild->removeMember($target, "You were kicked out of the Guild.");
                    $guild->sendLogMessage("[Guild]" .$sender->getName() . "kicked $target out of the Guild.");
                    $sender->sendMessage("[Guild] Successfully kicked $target out of the Guild.");
                }
                else $sender->sendMessage("[Guild] $target is not part of the guild.");
            }
            else $sender->sendMessage("[Guild] You do not have permission to execute this command.");
        }
        else $sender->sendMessage("You are not in a guild.");
    }

    public static function invite(Session $sender, string $target): void
    {

    }

    public static function accept(Session $sender, string $guild): void
    {

    }

    public static function decline(Session $sender, string $guild): void
    {

    }

    public static function transfer(Session $sender, string $target): void
    {
        if($sender->checkAvailability("guild"))
        {
            if($sender->hasGuildPermission("guildAdmin"))
            {
                $guild = $sender->getGuild();
                if($guild->isGuildMember($target))
                {
                    $guild->transferOwnership($target);
                    $sender->sendMessage("[Guild] Successfully transferred the Guild's ownership to $target.");
                    if(SessionManager::isOnline($target))
                        SessionManager::getSessionByName($target)->sendMessage("[Guild] The Guild's ownership was transferred to you! You are the new Guildmaster!");
                }
                else $sender->sendMessage("[Guild] $target is not part of the guild.");
            }
            else $sender->sendMessage("[Guild] You do not have permission to execute this command.");
        }
        else $sender->sendMessage("You are not in a guild.");
    }
}