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

namespace Society;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Config;

use Society\guild\GuildManager;
use Society\party\PartyManager;
use Society\database\mysql\MySQLDatabase;
use Society\commands\friends\FriendsCommand;
use Society\commands\party\PartyCommand;
use Society\commands\guild\GuildCommand;

class Society extends PluginBase
{
    use SingletonTrait;

    private Config $settings;

    public function onLoad(): void
    {
        self::setInstance($this);
    }

    public function onEnable(): void
    {
        #Init settings
        $this->settings = new Config($this->getResourceFolder() . "settings.yml", Config::YAML);

        $this->getServer()->getPluginManager()->registerEvents(new SocietyListener(), $this);
        $this->registerCommands();
        $this->initStaticClasses();
        $this->initDatabases();
        MySQLDatabase::loadGuilds();

        $this->getLogger()->notice("[~] Plugin is on!");
    }

    public function onDisable(): void
    {
        # MySQL Database disconnection
        MySQLDatabase::disconnect();
        $this->getLogger()->notice('[~] Connection with MySQL Database terminated.');
    }

    public function registerCommands(): void
    {
        $commands = [
            new FriendsCommand(),
            new PartyCommand(),
            new GuildCommand()
        ];

        $this->getServer()->getCommandMap()->registerAll("society", $commands);
    }

    protected function initStaticClasses(): void
    {
        MySQLDatabase::initClass();
        GuildManager::initClass();
        PartyManager::initClass();
    }

    protected function initDatabases(): void
    {
        # MySQL Database initialization
        MySQLDatabase::connect();
        MySQLDatabase::check();
    }

    public function getSettings(): Config
    {
        return $this->settings;
    }
}