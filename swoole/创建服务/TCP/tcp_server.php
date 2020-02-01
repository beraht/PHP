<?php
//创建Server对象，监听 127.0.0.1:9501端口
$serv = new Swoole\Server("127.0.0.1", 9503); 

//用于设置运行时的各项参数
$serv->set(array(
    'reactor_num' => 2, //通过此参数来调节Reactor线程的数量，以充分利用多核
    'worker_num' => 4,    //worker进程数
    'backlog' => 128,   //listen backlog
    'max_request' => 50, //最大连接
));

/**
 * 监听连接进入事件
 * 服务器可以同时被成千上万个客户端连接，$fd就是客户端连接的唯一标识符
 * reactor_id  线程ID
 **/
$serv->on('Connect', function ($serv, $fd,$reactor_id) {  
    echo "客户端连接标识:".$fd ."\n";
    echo "线程ID:".$reactor_id ."\n";
});

/**
 * 监听数据接收事件
 *  $fd就是客户端连接的唯一标识符
 *  reactor_id  线程ID
 *  data 是接收的数据
 **/
$serv->on('Receive', function ($serv, $fd, $reactor_id, $data) {
    echo "客户端连接标识:".$fd ."\n";
    echo "线程ID:".$reactor_id ."\n";
    echo "接收的数据是:".$data ."\n";
    $serv->send($fd, "Server: ".$data);
});

//监听连接关闭事件
$serv->on('Close', function ($serv, $fd) {
    echo "Client: Close.\n";
});

//启动服务器
$serv->start();