<?php

namespace PhotonPHP\Socket\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * @method static $this on($event, $callback) 绑定接收消息
 */
class SocketIo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'socket.io';
    }
}
