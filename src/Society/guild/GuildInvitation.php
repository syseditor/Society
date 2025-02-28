<?php

/*
 * Society - A Pocketmine plugin
 * Copyright © 2025 Dimitrios Kakagiannis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *          http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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