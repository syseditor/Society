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