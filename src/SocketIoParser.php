<?php

namespace PhotonPHP\Socket;

use Closure;
use Illuminate\Support\Facades\Config;

class SocketIoParser
{
    public $id;
    protected $server;
    protected $events = [];
    private $ackId;


    protected $to = [];

    /**
     * register socketio event
     * @param $event
     * @param Closure $callback
     */
    public function on($event, Closure $callback)
    {
        if (is_string($event)) {
            $this->events[$event] = $callback;
        }
    }
    public function to($values)
    {
        $values = is_string($values) || is_integer($values) ? func_get_args() : $values;
        foreach ($values as $value) {
            if (!in_array($value, $this->to)) {
                $this->to[] = $value;
            }
        }
        return $this;
    }
    public function reset()
    {
        $this->to = [];
        return $this;
    }

    /**
     * listen server event
     * @param $server
     */
    public function bindEngine($server)
    {
        $server->on('Open', [$this, 'onOpen']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
    }
    public function onOpen($server, $request)
    {
        $this->server = $server;
        $this->id = $request->fd;
        $data = [
            'sid' => base64_encode(uniqid()),
            'upgrades' => ['websocket'],
            'pingInterval' => (int) Config::get('swoole_websocket.ping_interval', 25000),
            'pingTimeout' => (int) Config::get('swoole_websocket.ping_timeout', 60000)
        ];
        $server->push($request->fd, '0' . json_encode($data));
        $server->push($request->fd, '40');
        if (isset($this->events['connection'])) {
            $this->events['connection']($this);
        }
    }
    public function onClose($server, $fd)
    {
        $this->server = $server;
        $this->id = $fd;
        if (isset($this->events['disconnect'])) {
            $this->events['disconnect']($this);
        }
    }
    public function onMessage($server, $frame)
    {
        $this->server = $server;
        $this->id = $frame->fd;
        if ($index = strpos($frame->data, '[')) {
            $code = substr($frame->data, 0, $index);
            $data = json_decode(substr($frame->data, $index), true);
        } else {
            $code = $frame->data;
            $data = '';
        }
        switch (mb_strlen($code)) {
            case 0:
                break;
            case 1:
                switch ($code) {
                    case '2':   //client ping
                        $server->push($frame->fd, '3'); //sever pong
                        break;
                }
                break;
            case 2:
                switch ($code) {
                    case '41':   //client disconnect
                        $this->close();
                        break;
                    case '42':   //client message
                        if (isset($this->events[$data[0]])) {
                            $this->events[$data[0]]($this, $data[1]);
                        }
                        break;
                }
                break;
            default:
                switch ($code[0]) {
                    case '4':   //client message
                        switch ($code[1]) {
                            case '2':   //client message with ack
                                $this->ackId = substr($code, 2);
                                $this->events[$data[0]]($this, $data[1], [$this, 'ack']);
                                break;
                            case '3':   //client reply to message with ack
                                break;
                        }
                        break;
                }
                break;
        }
    }
    public function emit($event, $data)
    {
        return $this->server->push($this->id, '42' . json_encode([$event, $data]));
    }
    public function emitTo($event, $data, $clientId)
    {
        return $this->server->push($clientId, '42' . json_encode([$event, $data]));
    }
    public function disconnect()
    {
        $this->server->push($this->id, '41');
        $this->close();
    }
    public function ack($data)
    {
        $this->server->push($this->id, '43' . $this->ackId . json_encode($data));
    }
    public function close()
    {
        $sever = $this->server;
        $id = $this->id;
        $this->server->after(2000, function () use ($sever, $id) {
            $sever->close($id);
        });
    }
}
