<?php

namespace PhotonPHP\Socket\Commands;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use PhotonPHP\Socket\SocketIoServer;

class Socket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:socket {action : start|stop|restart|stats}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Swoole Socket.io Server';
    protected $config;
    protected $SocketIo;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SocketIoServer $SocketIoServer)
    {
        $this->SocketIo = $SocketIoServer;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$this->config = config('socket_io');

        $action = $this->argument('action');
        if (!in_array($action, ['start', 'stop', 'restart','stats'], true)) {
            $this->error(
                "命令 '{$action}'.不正确, 仅支持 'start', 'stop', 'restart','stats'"
            );
            return;
        }
        $this->SocketIo->{$action}($this);
    }
}
