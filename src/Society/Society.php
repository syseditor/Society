<?php

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