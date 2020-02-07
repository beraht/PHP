<?php
use Swoole\Http\Server;

$http = new Server("0.0.0.0", 9506);

$http->set(
    [
        'enable_static_handler' => true, //开启静态文件处理
        'document_root' => "/home/swoole/public/html", //静态文件存放的目录
    ]
);
//如果请求的静态文件,在指定的目录下找到,该文件,那么久不会在走下面的逻辑了.

$http->on('request', function ($request, $response) {

    //获取请求的所有数据
    print_r($request);

    //获取请求的参数
    print_r($request->get);

    //设置cookie
    $response->cookie("age",100);

    //发送响应体
    $response->end("<h1>发送Http响应体:Hello Swoole. #".rand(1000, 9999)."</h1>");


});
$http->start();