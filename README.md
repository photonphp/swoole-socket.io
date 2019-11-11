# swoole-socket.io
基于swoole实现socket.io服务端

仅支持 Laravel 6


## 安装



~~~php

composer require photonphp/swoole-socket.io

~~~

## 导入文件

~~~php

php artisan vendor:publish --tag=swoole-socket.io

~~~

## 命令

~~~php
//启动
php artisan swoole:socket start
//停止
php artisan swoole:socket stop
//重启
php artisan swoole:socket restart
//查看状态
php artisan swoole:socket stats
~~~
成功启动后如
~~~php
socket.io 服务进程:1	已启动
socket.io 服务进程:2	已启动
socket.io 服务进程:3	已启动
socket.io 服务进程:4	已启动
socket.io 服务进程:5	已启动
socket.io 服务进程:6	已启动
socket.io 服务进程:7	已启动
socket.io 服务进程:8	已启动
socket.io 服务进程:9	已启动
socket.io 服务进程:10	已启动
socket.io 服务进程:11	已启动
socket.io 服务进程:12	已启动
~~~
##  方法
修改
routes/socket.io.php

~~~php
SocketIo::on('connection', function ($socket) {
    echo '连接:' . $socket->id . PHP_EOL;
    //发送消息
    $socket->emit('message', ['test' => 'Hello, world!']);
});
SocketIo::on('disconnect', function ($socket) {
    echo '关闭:' . $socket->id . PHP_EOL;
});
//接收消息
SocketIo::on('message', function ($socket, $data) {
    echo 'message:' . PHP_EOL;
    //发送消息
    $socket->emit('message', ['hello' => 'message received']);
});

SocketIo::on('login','HomeController@login');
//or
SocketIo::on('login',[HomeController::class, 'login']);

~~~