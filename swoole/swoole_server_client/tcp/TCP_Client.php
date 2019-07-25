<?php
    //连接swoole tcp 服务
   $client = new swoole_client(SWOOLE_SOCK_TCP);

   if(!$client->connect('127.0.0.1',9511)){
        echo "连接失败";
        exit;
   };

   //php cli
   fwrite(STDOUT,'请输入消息:');
   $msg = trim(fgets(STDOUT));

   //发送消息给 tcp server服务器
   $client->send($msg);

   //接收来自tcp_server的数据
   $reult = $client->recv();

   echo $reult;
?>