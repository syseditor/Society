<?php

namespace Society;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Config;

use Society\listeners\SessionListener;

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

    }

    public function getSettings(): Config
    {
        return $this->settings;
    }
}