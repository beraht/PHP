<?php
//实例化 http_server服务, 监听多个地址 , 端口号是9501
$http = new Swoole\Http\Server("0.0.0.0", 8811);

/**
 * 类似于nginx 和 apache 一样,对静态文件和动态文件的处理
 */
$http->set(
    
   [
    //开启静态文件请求处理功能
    'enable_static_handler' => true,
    //静态文件的路径
    'document_root' => '/root/swoole/demo', // v4.4.0以下版本, 此处必须为绝对路径
   ] 
);

/***************   上面走的是静态模式,下面走的PHP动态模式    *******************************/

/* 
*request标示监听请求的类型 , 
* 回调函数里面 $request 表示请求的内容
*            $response 表示响应的内容
*/ 
$http->on('request', function ($request, $response) {
    //获取请求的参数
   // print_r($request->get);

    /**发送Http响应体，并结束请求处理
     * 设置cookie及有效时间
     * 接收来自客户端的参数
     */
   $response->cookie('userinfo',"xsssss",time()+3600);//设置cookie
   $response->end(json_encode($request->get));
});

//开始服务
$http->start();



/**
 * 在客户端 或者浏览器请求的方式
 * 
 * //动态方式请求
 * liunx 中 curl curl 127.0.0.1:8811/?m=2&type=3 携带参数访问
 * 浏览器中直接 xxx.com:8811/?m=2&type=3
 * 
 * //静态方式请求
 * liunx curl 127.0.0.1:8811/index.html
 * 浏览器中直接 xxx.com:8811/index.html 会获取对应的静态文件
 *            xxx.com:8811/ss/index.html 表示在swoole设置的指定路径下ss目录下的index.html
 */



?>

