<?php

namespace Society\commands\guild;

use Society\database\mysql\MySQLDatabase;
use Society\session\Session;
use Society\session\SessionManager;
use Society\guild\Guild;
use Society\guild\GuildManager;

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
}