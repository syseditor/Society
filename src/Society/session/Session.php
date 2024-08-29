<?php

namespace Society\session;

use pocketmine\player\Player;

use Society\party\Party;
use Society\guild\Guild;
use Society\guild\GuildRole;

class Session
{
    private Player $player;
    private null|Party $party;
    private null|Guild $guild;
    private null|GuildRole $guildRole;
    private bool $isOnParty;
    private bool $isOnGuild;
    private array $friendlist = [];

    public function __construct(Player $player) #DAMN CHECK THE DAMN GUILD
    {
        $this->player = $player;
        $this->party = null;
        $this->guild = null;
        $this->guildRole = null;
        $this->isOnParty = false;
        $this->isOnGuild = !is_null($this->guild); #ALWAYS FALSE, PRIOR TO CHANGES
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

    public function getGuildRole(): null|GuildRole
    {
        return $this->guildRole;
    }

    public function getFriendList(): ?array
    {
        return $this->friendlist;
    }

    public function checkAvailability(string $option): bool
    {
        return match ($option) {
            'party' => $this->isOnParty,
            'guild' => $this->isOnGuild,
            default => false,
        };
    }

    public function setFriendlist(?array $friendlist): void
    {
        $this->friendlist = $friendlist;
    }

    public function setGuild(null|Guild $guild): void
    {
        $this->guild = $guild;
    }

    public function setGuildRole(null|GuildRole $role): void
    {
        $this->guildRole = $role;
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