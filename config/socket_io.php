<?php
return [
    'host' => env('SWOOLE_SOCKET_HOST', '0.0.0.0'),
    'port' => env('SWOOLE_SOCKET_PORT', '9501'),
    'mode' =>  env('SWOOLE_SOCKET_PROCESS', SWOOLE_PROCESS),
    'sock_type' => env('SWOOLE_SOCKET_SOCK_TCP', SWOOLE_SOCK_TCP),
    'ping_interval' => 25000,
    'ping_timeout' => 60000,
    'swoole' => [
        //swoole配置 具体参考官网，可自行增加配置
        'reactor_num' => env('SWOOLE_SOCKET_REACTOR_NUM', swoole_cpu_num()),
        'worker_num' => env('SWOOLE_SOCKET_WORKER_NUM', swoole_cpu_num())
    ]
];
