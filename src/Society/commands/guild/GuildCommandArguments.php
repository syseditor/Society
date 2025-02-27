<?php

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
            $maxmembers = $guild->getMaxMembersAllowed();
            $membercount = $guild->getMemberCount();

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
                if($guild->hasGivenPermissionToDisband())
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
}