<?php

namespace PhotonPHP\Socket;

use Closure;
use Swoole\Process;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use PhotonPHP\Socket\Facades\SocketIo;
use PhotonPHP\Socket\Facades\SocketIoClient;
use Swoole\WebSocket\Server as WebSocketServer;

class SocketIoServer
{
    protected $config;
    public function __construct($config)
    {
        $this->config = $config;
    }



    public function start(Command $Command)
    {
        if (SocketIoClient::isRunning()) {
            if ($Command->confirm('socket.io 服务已启动，是否重启?')) {
                $this->stop($Command);
            } else {
                exit;
            }
        }

        $server = $this->getServer();
        $socketioHandler = new SocketIoParser;

        foreach (SocketIo::get() as $key => $value) {
            if (is_string($value) || is_array($value)) {
                $value = function ($socket, $data) use ($value) {
                    return action($value, [$socket, $data]);
                };
            }

            $socketioHandler->on($key, $value);
        }

        $socketioHandler->bindEngine($server);

        $server->on('Start', function ($socket) use ($Command) {
            Cache::put('swoole:socket.io.pid', [$socket->master_pid, $socket->manager_pid]);
            Cache::put('swoole:socket.io.increment.count', 0);
            Cache::put('swoole:socket.io.connect.count', 0);
        });
        $server->on('Shutdown', function ($socket) use ($Command) {
            $Command->info('socket.io 服务已关闭');
        });
        $server->on('WorkerStart', function ($serv, $worker_id) use ($Command) {
            $Command->info('socket.io 服务进程:' . ($worker_id + 1) . "\t已启动");
        });
        $server->on('WorkerStop', function ($serv, $worker_id) use ($Command) {
            $Command->info('socket.io 服务进程:' . ($worker_id + 1) . "\t已关闭");
        });

        $server->on('Packet', function ($server, string $data, array $client_info) {
            var_dump($client_info);
        });

        $server->on('connect', function ($serv, $fd) {
            Cache::increment('swoole:socket.io.increment.count');
            Cache::increment('swoole:socket.io.connect.count');
        });
        $server->on('close', function ($serv, $fd) {
            Cache::decrement('swoole:socket.io.connect.count');
        });
        
        




        $server->start();


        //$server->start();
    }
    public function stop(Command $Command)
    {
        if (!SocketIoClient::isRunning()) {
            $Command->error('socket.io 服务未启动');

            return;
        }

        $Command->info('正在关闭...');

        if ($this->killProcess(SIGTERM, 15)) {
            $Command->error('关闭失败');

            return;
        }

        $Command->info('关闭成功');
    }
    public function restart(Command $Command)
    {
        if (SocketIoClient::isRunning()) {
            $this->stop($Command);
        }

        $this->start($Command);
    }
    public function stats(Command $Command)
    {
        $table = [
            'PHP 版本' => phpversion(),
            'Swoole 版本' => swoole_version(),
            'socket.io 状态' => SocketIoClient::isRunning() ? '启动' : '关闭',
            'socket.io 服务' => Arr::get($this->config, 'host', '0.0.0.0'),
            'socket.io 端口' => Arr::get($this->config, 'port', 9501),
            '累计链接' => SocketIoClient::getCountIncrement(),
            '当前链接' => SocketIoClient::getCountConnect()

        ];

        foreach ($table as $key => $value) {
            $Command->info("{$key}\t{$value}");
        }
    }


    /**
     * Kill process.
     *
     * @param int $sig
     * @param int $wait
     *
     * @return bool
     */
    protected function killProcess($sig, $wait = 0)
    {
        Process::kill(
            Arr::first(Cache::get('swoole:socket.io.pid', [])),
            $sig
        );

        if ($wait) {
            $start = time();

            do {
                if (!SocketIoClient::isRunning()) {
                    break;
                }

                usleep(100000);
            } while (time() < $start + $wait);
        }

        return SocketIoClient::isRunning();
    }



    protected function getServer(): WebSocketServer
    {
        $host = Arr::get($this->config, 'host', '0.0.0.0');
        $port = Arr::get($this->config, 'port', 9501);
        $processType = Arr::get($this->config, 'mode', SWOOLE_PROCESS);
        $socketType = Arr::get($this->config, 'sock_type', SWOOLE_SOCK_TCP);
        $server = new WebSocketServer((string) $host, (int) $port, (int) $processType, (int) $socketType);
        $server->set(Arr::get($this->config, 'swoole', [
            'reactor_num' => swoole_cpu_num(),
            'worker_num' => swoole_cpu_num()
        ]));
        return $server;
    }
}
