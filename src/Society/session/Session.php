<?php

namespace Society\session;

use pocketmine\player\Player;

class Session
{
    private Player $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }
}