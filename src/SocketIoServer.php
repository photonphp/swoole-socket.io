<?php

namespace PhotonPHP\Socket;

use Closure;
use Swoole\Process;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use PhotonPHP\Socket\Facades\SocketIo;
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
        if ($this->isRunning()) {
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

        $server->on('start', function ($socket) use ($Command) {
            Cache::put('swoole:socket.io.pid', [$socket->master_pid, $socket->manager_pid]);
        });
        $server->on('shutdown', function ($socket) use ($Command) {
            $Command->info('socket.io 服务已关闭');
        });
        $server->on('WorkerStart', function ($serv, $worker_id) use ($Command) {
            $Command->info('socket.io 服务进程:' . ($worker_id + 1) . "\t已启动");
        });
        $server->on('WorkerStop', function ($serv, $worker_id) use ($Command) {
            $Command->info('socket.io 服务进程:' . ($worker_id + 1) . "\t已关闭");
        });



        $server->start();


        //$server->start();
    }
    public function stop(Command $Command)
    {
        if (!$this->isRunning()) {
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
        if ($this->isRunning()) {
            $this->stop($Command);
        }

        $this->start($Command);
    }
    public function stats(Command $Command)
    {
        //$server->stats();
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
                if (!$this->isRunning()) {
                    break;
                }

                usleep(100000);
            } while (time() < $start + $wait);
        }

        return $this->isRunning();
    }


    protected function isRunning()
    {
        $pids = Cache::get('swoole:socket.io.pid', []);

        if (!count($pids)) {
            return false;
        }

        $masterPid = $pids[0] ?? null;
        $managerPid = $pids[1] ?? null;

        if ($managerPid) {
            // Swoole process mode
            return $masterPid && $managerPid && Process::kill((int) $managerPid, 0);
        }

        // Swoole base mode, no manager process
        return $masterPid && Process::kill((int) $masterPid, 0);
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
