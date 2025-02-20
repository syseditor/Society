<?php

namespace Society\session;

use pocketmine\player\Player;

use Society\commands\friends\utils\FriendInvitation;
use Society\database\mysql\MySQLDatabase;
use Society\party\Party;
use Society\party\PartyRole;
use Society\guild\Guild;
use Society\guild\GuildRole;
use Society\utils\Utils;

class Session
{
    private Player $player;
    private ?Party $party;
    private ?PartyRole $partyRole;
    private ?Guild $guild;
    private ?GuildRole $guildRole;
    private bool $isOnParty;
    private bool $isOnGuild;
    private array $friendlist = [];
    private array $friendInvitesSent = [];
    private array $friendInvitesReceived = [];

    public function __construct(Player $player) #DAMN CHECK THE DAMN GUILD
    {
        $this->player = $player;
        $this->party = null;
        $this->partyRole = null;
        $this->guild = null;
        $this->guildRole = null;
        $this->isOnParty = false;
        $this->isOnGuild = !is_null($this->guild); #ALWAYS FALSE DUE TO VARIABLE DECLARATION, PRIOR TO CHANGE
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getName(): string
    {
        return $this->player->getName();
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function getPartyRole(): ?PartyRole
    {
        return $this->partyRole;
    }

    public function getGuild(): ?Guild
    {
        return $this->guild;
    }

    public function getGuildRole(): ?GuildRole
    {
        return $this->guildRole;
    }

    public function getFriendList(): array
    {
        return $this->friendlist;
    }

    public function getFriendInvitesSent(): array
    {
        return $this->friendInvitesSent;
    }

    public function getFriendInvitesReceived(): array
    {
        return $this->friendInvitesReceived;
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

    public function setGuild(?Guild $guild): void
    {
        $this->guild = $guild;
    }

    public function setGuildRole(?GuildRole $role): void
    {
        $this->guildRole = $role;
    }

    public function addToParty(Party $party): void
    {
        //TODO: start building it ig
    }

    public function setPartyRole(PartyRole $role): void
    {
        $this->partyRole = $role;
    }

    public function addToGuild(Guild $guild): void
    {
        //TODO: start building it ig
    }

    public function addFriend(Session $session, string $type): void
    {
        $name = $session->getPlayer()->getName();
        $id = $session->getPlayer()->getUniqueId()->getInteger();

        $friendList = $this->getFriendList();
        $i = 0;
        while(!is_null($friendList[$i]) && $i < 10) $i++;

        $slot = Utils::$friendSlotPositions[$i];

        $friendList[$i] = $name;
        var_dump($friendList); //to-remove

        MySQLDatabase::insert("Friends", $slot, $id, $this);

        $this->sendMessage("Successfully added $name to your friend list");
        if ($session->getPlayer()->isOnline()) $session->sendMessage("Successfully added ".$this->getPlayer()->getName()." to your friend list");

        switch ($type)
        {
            case 'sent' or 'Sent':
                unset($this->friendInvitesSent[$name]);
                break;
            case 'received' or 'Received':
                unset($this->friendInvitesReceived[$name]);
                break;
        }
    }

    public function removeFromParty(): void
    {
        //TODO: start building it ig
    }

    public function removeFromGuild(): void
    {
        //TODO: start building it ig
    }

    public function removeFriend(string $name, string $cause): void
    {
        //TODO: start building it ig
    }

    public function sendFriendInvitation(FriendInvitation $invitation): void
    {
        $receiver = $invitation->getReceiver();
        $name = $receiver->getPlayer()->getName();

        $this->friendInvitesSent[$name] = $invitation;
        $this->sendMessage("Successfully sent a friend request to $name");
    }

    public function receiveFriendInvitation(FriendInvitation $invitation): void
    {
        $sender = $invitation->getReceiver();
        $name = $sender->getPlayer()->getName();

        $this->friendInvitesReceived[$name] = $invitation;
        $this->sendMessage("$name wants to become your friend! Type `/friend accept $name` to accept OR `/friend decline $name` to decline");
    }

    public function removeFriendInvitation(string $player, string $cause, string $type): void
    {
        switch ($cause)
        {
            case 'decline':
                switch ($type)
                {
                    case 'sent' or 'Sent':
                        unset($this->friendInvitesSent[$player]);
                        $this->sendMessage("$player declined your friend request.");
                        break;
                    case 'received' or 'Received':
                        unset($this->friendInvitesReceived[$player]);
                        $this->sendMessage("You declined $player's friend request.");
                        break;

                }
                break;
            case 'abort':
                switch ($type)
                {
                    case 'sent' or 'Sent':
                        unset($this->friendInvitesSent[$player]);
                        $this->sendMessage("You aborted your friend request to $player.");
                        break;
                    case 'received' or 'Received':
                        unset($this->friendInvitesReceived[$player]);
                        $this->sendMessage("$player aborted their friend request.");
                        break;

                }
                break;
        }
    }

    public function sendMessage(string $message): void
    {
        $this->player->sendMessage($message);
    }
}