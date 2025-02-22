<?php

namespace Society;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;

use pocketmine\utils\TextFormat;
use Society\session\SessionManager;
use Society\utils\Utils;

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

    public function onChat(PlayerChatEvent $event): void
    {
        $session = SessionManager::getSessionByName($event->getPlayer()->getName());
        $currentChat = $session->getCurrentChat();

        if($currentChat === 1)
        {
            $event->cancel();
            $message = $event->getMessage();
            $session->getParty()->broadcastMessage(TextFormat::GRAY . "[Party Chat] " . Utils::roleToColor($session->getPartyRole()) . $session->getName() . TextFormat::RESET ." >> " . $message);
        }
        else if($currentChat === 2)
        {
            $event->cancel();
            $message = $event->getMessage();
            $session->getParty()->broadcastMessage(TextFormat::LIGHT_PURPLE . "[Guild Chat] " . Utils::roleToColor($session->getPartyRole()) . $session->getName() . TextFormat::RESET . " >> " . $message);
        }
    }
}