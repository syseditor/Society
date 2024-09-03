<?php

namespace Society\commands\friends\utils;

use Society\session\Session;

class FriendInvitation
{
    private Session $receiver;
    private Session $sender;

    public function __construct(Session $sender, Session $receiver)
    {
        $this->receiver = $receiver;
        $this->sender = $sender;
    }

    public function getSender(): Session
    {
        return $this->sender;
    }

    public function getReceiver(): Session
    {
        return $this->receiver;
    }
}