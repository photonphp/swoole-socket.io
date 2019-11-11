<?php

namespace PhotonPHP\Socket;

class SocketIo
{
    protected $events = [];
    public function on(string $event, $callback): SocketIo
    {
        $this->events[$event] = $callback;
        return $this;
    }
    public function get(string $event = null): array
    {
        return $this->events[$event] ?? $this->events;
    }
}
