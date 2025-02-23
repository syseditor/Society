<?php

namespace Society\guild;

use Society\session\Session;

class GuildInvitation
{
    private Session $inviter;
    private Session $receiver;
    private Guild $guild;

    public function __construct(Session $inviter, Session $receiver, Guild $guild)
    {
        $this->inviter = $inviter;
        $this->receiver = $receiver;
        $this->guild = $guild;
    }

    public function getInviter(): Session
    {
        return $this->inviter;
    }

    public function getReceiver(): Session
    {
        return $this->receiver;
    }

    public function getGuild(): Guild
    {
        return $this->guild;
    }
}