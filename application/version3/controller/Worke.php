<?php
/**
 * Created by PhpStorm.
 * User: wangfuruo
 * Date: 2017/9/4
 * Time: 下午1:14
 */
namespace app\version3\controller;
use think\cache\driver\Redis;
use think\Request;
use think\worker\Server;
use Workerman\Worker;
use \Workerman\Lib\Timer;
define('HEARTBEAT_TIME', 25);

class Worke
{
    function heart(){
        require_once '/usr/local/apache/htdocs/yanku5/vendor/workerman/workerman/Autoloader.php';

        Worker::$logFile = '/usr/local/apache/htdocs/yanku5/workerman.log';
        // 创建一个Worker监听2347端口，不使用任何应用层协议
        $tcp_worker = new Worker("tcp://0.0.0.0:2346");

        $tcp_worker->onMessage = function ($connection, $data) {
            // 给connection临时设置一个lastMessageTime属性，用来记录上次收到消息的时间
            $connection->lastMessageTime = time();
            // 其它业务逻辑...
            if ($data!=''){
                var_dump($data);
                $redis = new \Redis();
                $redis->connect("localhost", 6379);
                $redis->select(2);
                $time = strtotime("now");
                $data = json_decode($data,true);
                if ($data['msg_type']!=0){
                    $data = json_encode($data);
                    foreach($connection->worker->connections as $con)
                    {
                        $con->send($data);
                    }

                }else{
                    $key = $redis->get($data['user_id']);
                    if ($key) {
                        $key = json_decode($key, true);
                        $key['count'] = $data['view'];
                        $key['praise'] = $data['praise'];
                        $key['kubi'] = $data['kubi'];
                        $key['isRtcing'] = $data['isRtcing'];
                        $key['rtcAnchorID'] = $data['rtcAnchorID'];
                        $key = json_encode($key, JSON_UNESCAPED_SLASHES);
                        $redis->set($data['user_id'], $key);
                    }
                    $redis->select(4);
                    $id = $connection->id;
                    $redis->hSet($id, 'yanku',$data['user_id']);
                    $redis->hSet($data['user_id'],'yanku',$id);
                }
            }



        };
            // 进程启动后设置一个每秒运行一次的定时器
        $tcp_worker->onWorkerStart = function($tcp_worker) {
            Timer::add(1, function()use($tcp_worker){
                $time_now = time();
                foreach($tcp_worker->connections as $connection) {
                    // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                    if (empty($connection->lastMessageTime)) {
                        $connection->lastMessageTime = $time_now;
                        continue;
                    }
                    // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                    if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
//                        $connection->close();
                    }
                }
            });
        };
        $tcp_worker->onClose = function ($connection) {
                $id = $connection->id;
                $redis = new \Redis();
                $redis->connect("localhost", 6379);
                $redis->select(4);
                $user_id = $redis->hGet($id,'yanku');
                $redis->hDel($id,'yanku');
                $redis->hDel($user_id,'yanku');
                $redis->select(2);
                $data = $redis->get($user_id);
                $data = json_decode($data, true);
                $type = $data['channel_type'];
                $redis->del($user_id);
                $redis->select(3);
                $redis->sRem($type, $user_id);
        };

        Worker::runAll();
    }
}

