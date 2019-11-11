<?php

namespace PhotonPHP\Socket\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * @method static this  emit($event, $data) 发送消息
 * @method static $this emitTo($event, $data, $clientId) 发送消息给指定连接
 * @method static void disconnect($event, $callback) 关闭当前连接
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
