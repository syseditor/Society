<?php

namespace Society\session;

use pocketmine\player\Player;

use Society\party\Party;
use Society\guild\Guild;

class Session
{
    private Player $player;
    private null|Party $party;
    private null|Guild $guild;
    private bool $isOnParty;
    private bool $isOnGuild;

    public function __construct(Player $player, null|Guild $guild)
    {
        $this->player = $player;
        $this->party = null;
        $this->guild = $guild;
        $this->isOnParty = false;
        $this->isOnGuild = !is_null($guild);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getParty(): null|Party
    {
        return $this->party;
    }

    public function getGuild(): null|Guild
    {
        return $this->guild;
    }

    public function getFriendList(): ?array
    {
        //TODO
        return null;
    }

    public function checkAvailability(string $option): bool
    {
        return match ($option) {
            'party' => $this->isOnParty,
            'guild' => $this->isOnGuild,
            default => false,
        };
    }

    public function addToParty(Party $party): void
    {
        //TODO: start building it ig
    }

    public function addToGuild(Guild $guild): void
    {
        //TODO: start building it ig
    }

    public function addFriend(Player $player): void
    {
        //TODO: start building it ig
    }

    public function removeFromParty(): void
    {
        //TODO: start building it ig
    }

    public function removeFromGuild(): void
    {
        //TODO: start building it ig
    }

    public function removeFriend(string $name): void
    {
        //TODO: start building it ig
    }
}