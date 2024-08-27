<?php

namespace Society;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Config;

use Society\listeners\SessionListener;
use Society\database\mysql\MySQLDatabase;

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

        $this->registerListeners();
        $this->initDatabases();

        $this->getLogger()->notice("[~] Plugin is on!");
    }

    public function onDisable(): void
    {
        # MySQL Database disconnection
        MySQLDatabase::disconnect();
        $this->getLogger()->notice('[~] Connection with MySQL Database terminated.');
    }

    protected function registerListeners(): void
    {
        $listeners = [
            new SessionListener()
        ];

        foreach ($listeners as $listener)
        {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
    }

    protected function initDatabases(): void
    {
        # MySQL Database initialization
        MySQLDatabase::initClass();
        MySQLDatabase::connect();
        MySQLDatabase::check();
    }

    public function getSettings(): Config
    {
        return $this->settings;
    }
}