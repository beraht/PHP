<?php

//创建Server对象，监听 127.0.0.1:9502端口，类型为SWOOLE_SOCK_UDP
$serv = new swoole_server("127.0.0.1", 9506, SWOOLE_PROCESS, SWOOLE_SOCK_UDP); 

/**
 * 监听数据接收事件
 * $clientInfo是客户端的相关信息，是一个数组，有客户端的IP和端口等内容
 * 调用 $server->sendto 方法向客户端发送数据
 **/
$serv->on('Packet', function ($serv, $data, $clientInfo) {
    var_dump($clientInfo).PHP_EOL;
    echo "发送的数据: ".$data.PHP_EOL;
    $serv->sendto($clientInfo['address'], $clientInfo['port'], "Server ".$data);
    
});

//启动服务器
$serv->start();