<?php
/**
 * 协程可以理解为纯用户态的线程
 * 使用的范围Server、Http\Server、WebSocket\Server进行开发，
 * 底层在onRequet, onReceive, onConnect等事件回调之前自动创建一个协程，在回调函数中使用协程API
 */

 /**
  * 通过同步的方法实现异步io的效果和性能
    * $redis = new Swoole\Coroutine\Redis();
    * $redis->connect('127.0.0.1',6379);
    * $redis->get('name');
  */

  //实例化 http_server服务, 监听多个地址 , 端口号是9501
  $http = new Swoole\Http\Server("0.0.0.0", 8811);

 /* 
*request标示监听请求的类型 , 
* 回调函数里面 $request 表示请求的内容
*            $response 表示响应的内容
*/ 
$http->on('request', function ($request, $response) {
    //获取redis 里面key的值,然后输出到浏览器
    $redis = new Swoole\Coroutine\Redis();
    $redis->connect('127.0.0.1',6379);
    //获取客户端请求的参数k,并从redis中取出这个健的值
    $value = $redis->get($request->get['k']);
    //将值发送给客户端
    $response->header('Content-Type',"text/plain");
    $response->end($value);
}); 



//开始服务
$http->start();

?>

使用方法:
 在浏览器输入 www.xxxx.com?k=name  ,就会去获取数据