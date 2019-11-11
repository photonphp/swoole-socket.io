<?php

namespace PhotonPHP\Socket;

use Illuminate\Support\ServiceProvider;
use PhotonPHP\Socket\Commands\Socket;
use Swoole\WebSocket\Server as WebSocketServer;

class SocketIoProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/socket_io.php' => config_path('socket_io.php'),
            __DIR__ . '/../routes/socket.io.php' => base_path('routes/socket.io.php'),
        ], 'swoole-socket.io');

        $this->mergeConfigFrom(__DIR__ . '/../config/socket_io.php', 'socket_io');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Socket::class
            ]);
        }
    }
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->app->singleton(SocketIoServer::class, function () {
                return new SocketIoServer($this->app->make('config')->get('socket_io'));
            });
        }
        $this->app->singleton(SocketIo::class, function () {
            return new SocketIo();
        });

        $this->app->alias(SocketIo::class, 'socket.io');
        $this->app->alias(SocketIoClient::class, 'socket.io.client');
        

        $routes=base_path('routes/socket.io.php');
        if(file_exists($routes)){
            require $routes;
        }else{
            $this->loadRoutesFrom(__DIR__ . '/../routes/socket.io.php');
        }

        
    }
}
