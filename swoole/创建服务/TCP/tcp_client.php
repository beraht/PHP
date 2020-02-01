<?php

//连接swoole tcp 服务
$cli = new Swoole\Client(SWOOLE_TCP);

if ($cli->connect('127.0.0.1', 9501)) {

    echo "连接服务成功.....\n";

} else {
    echo "connect failed.";
    exit;
}


//PHP cli常量
fwrite(STDOUT,"请输入消息:");
$msg = trim(fgets(STDIN));

/**
 * 发送消息给tcp server 服务器
 * 成功发送返回的已发数据长度
 * **/

if($cli->send($msg)){
    echo "发送失败.....\n";
}

/**
 * 用于从服务器端接收数据
 */
$result = $cli->recv();
echo "tcp服务器返回数据: ".$result;

