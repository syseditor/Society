<?php

namespace Society;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

use Society\listeners\SessionListener;

class Society extends PluginBase
{
    use SingletonTrait;

    public function onLoad(): void
    {
        self::setInstance($this);
    }

    public function onEnable(): void
    {
       $this->registerListeners();
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
}