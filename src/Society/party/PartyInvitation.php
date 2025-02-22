<?php

namespace Society\party;

use Society\session\Session;

class PartyInvitation
{
    private Session $inviter;
    private Session $receiver;
    private Party $party;

    public function __construct(Session $inviter, Session $receiver, Party $party)
    {
        $this->inviter = $inviter;
        $this->receiver = $receiver;
        $this->party = $party;
    }

    public function getInviter(): Session
    {
        return $this->inviter;
    }

    public function getReceiver(): Session
    {
        return $this->receiver;
    }

    public function getParty(): Party
    {
        return $this->party;
    }
}