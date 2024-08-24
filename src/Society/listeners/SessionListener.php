<?php

namespace Society\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use Society\session\SessionManager;

class SessionListener implements Listener
{
    public function onLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        SessionManager::openSession($player);
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $session = SessionManager::getSessionByName($player->getName());
        SessionManager::closeSession($session);
    }
}