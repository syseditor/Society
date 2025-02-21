<?php

namespace Society;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use Society\session\SessionManager;

class SocietyListener implements Listener
{
    public function onLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();

        # Server-side actions
        SessionManager::openSession($player);
        Society::getInstance()->getLogger()->notice("[~] Session opened: $name");
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $session = SessionManager::getSessionByName($name);
        SessionManager::closeSession($session);
        Society::getInstance()->getLogger()->notice("[~] Session closed: $name");
    }
}