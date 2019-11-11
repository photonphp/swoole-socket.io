<?php

namespace PhotonPHP\Socket\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isRunning() 检测是socket.io服务是否启动
 * @method static int getCountIncrement() 返回累计链接数
 * @method static int getCountConnect() 返回当前链接数
 */
class SocketIoClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'socket.io.client';
    }
}
