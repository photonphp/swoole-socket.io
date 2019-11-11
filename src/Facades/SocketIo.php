<?php

namespace PhotonPHP\Socket\Facades;

use Illuminate\Support\Facades\Facade;
/**
 * @method static $this broadcast()
 * @method static $this to($values)
 * @method static $this join($rooms)
 * @method static $this leave($rooms)
 * @method static boolean emit($event, $data)
 * @method static $this in($room)
 * @method static $this on($event, $callback)
 * @method static array get($event)
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
