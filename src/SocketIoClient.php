<?php

namespace PhotonPHP\Socket;

use Swoole\Process;
use Illuminate\Support\Facades\Cache;

class SocketIoClient
{

    /**
     * 检测是socket.io服务是否启动
     */
    public function isRunning(): bool
    {
        $pids = Cache::get('swoole:socket.io.pid', []);

        if (!count($pids)) {
            return false;
        }

        $masterPid = $pids[0] ?? null;
        $managerPid = $pids[1] ?? null;

        if ($managerPid) {
            return $masterPid && $managerPid && Process::kill((int) $managerPid, 0);
        }
        return $masterPid && Process::kill((int) $masterPid, 0);
    }

    /**
     * 返回累计链接数
     */
    public function getCountIncrement(): int
    {
        return intval(Cache::get('swoole:socket.io.increment.count'));
    }
    /**
     * 返回当前链接数
     */
    public function getCountConnect(): int
    {
        return intval(Cache::get('swoole:socket.io.connect.count'));
    }
}
