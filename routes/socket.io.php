<?php


use Illuminate\Http\Request;
use PhotonPHP\Socket\Facades\SocketIo;



SocketIo::on('connection', function ($socket) {
    echo '连接:' . $socket->id . PHP_EOL;
    $socket->emit('message', ['test' => 'Hello, world!']);
});
SocketIo::on('disconnect', function ($socket) {
    echo '关闭:' . $socket->id . PHP_EOL;
});

SocketIo::on('message', function ($socket, $data) {
    echo 'message:' . PHP_EOL;
    $socket->emit('message', ['hello' => 'message received']);
});