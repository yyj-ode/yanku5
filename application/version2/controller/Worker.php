<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/9/4
 * Time: 下午1:14
 */
namespace app\version2\controller;
use think\worker\Server;

class Worker extends Server
{
    function worker(){
        require_once '/usr/local/apache/htdocs/yanku5/vendor/workerman/workerman/Autoloader.php';

// 创建一个Worker监听2347端口，不使用任何应用层协议
        $tcp_worker = new Worker("tcp://0.0.0.0:2346");
        Worker::$logFile = '/tmp/workerman.log';
        Worker::$stdoutFile = '/tmp/stdout.log';
// 启动4个进程对外提供服务
        $tcp_worker->count = 4;
// 当客户端发来数据时
        $tcp_worker->onMessage = function($connection, $data)
        {
            // 向客户端发送hello $data
            var_dump($data);
            $connection->send('receive success');
        };

// 运行worker
        Worker::runAll();
    }
}
