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
查看状态
~~~php
PHP 版本        7.3.11
Swoole 版本     4.4.12
socket.io 状态  启动
socket.io 服务  0.0.0.0
socket.io 端口  9501
累计链接        2
当前链接        2
~~~

##  方法
修改
routes/socket.io.php

~~~php
SocketIo::on('connection', function ($socket) {
    echo '连接:' . $socket->id . PHP_EOL;
    
    $socket->emit('message', ['test' => 'Hello, world!']);//发送消息
    $socket->emitTo('message', ['test' => 'Hello, world!'],$socket->id );// 发送消息给指定用户
    $socket->disconnect();// 关闭当前连接 关通知用户
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

## swoole socket.io 服务进程
PhotonPHP\Socket\Facades\SocketIo

~~~php
use PhotonPHP\Socket\Facades\SocketIo;

SocketIo::on($event, $callback);// 绑定接收消息

~~~

##  异步操作
PhotonPHP\Socket\Facades\SocketIoClient

~~~php
use PhotonPHP\Socket\Facades\SocketIoClient;

SocketIo::isRunning();// 检测是socket.io服务是否启动
SocketIo::getCountIncrement();// 返回累计链接数
SocketIo::getCountConnect();// 返回当前链接数
~~~