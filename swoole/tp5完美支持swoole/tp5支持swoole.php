<?php
/**
 *比如访问 xxx.com/index/index/test,swoole 无法识别
 * 会先进入到request回调事件,
 * 每当tp5访问时会先进入 public/index.php,
 *  onWorkerStart 此事件在Worker进程/Task进程启动时发生, 在启动swoole服务时,request回调事件前,将tp框架的内容加进去,加载base.php
 *  总的来说就是热加载tp5里面的文件和内容,而且swoole的http协议真正执行的时候是在request回调事件中执行,所以只需要加入tp5的文件
 */

$http->on('WorkerStart',function(swoole_server $server,$worker_id){
    // 加载基础文件
    require __DIR__ . '/../thinkphp/base.php';
    //如果引入start.php 会直接执行文件,会根据worker_num的数量执行多个进程

});

/**
 * 相应的适配
 * swoole的http获取参数的请求不是基于原生的,比如post,get等,而tp框架是基于原生的.
 * 
 * swoole 不会释放超全局变量,需要手动释放如$_GET  $_POST....
 * 
 * swoole 会把控制器和控制器的方法保存在变量中,下次访问都是之前的(每次访问的内容都是一样的), $http->close; 清空变量,同时也会清除掉 超全变量$_GET  $_POST....
 * $http->close 使用会报错
 * 
 * 另外一种解决(每次访问不同网址都是一样的内容)的方法
 * 注释掉 Request.php 下的 path() 和 pathinfo()  方法下的   if(is_null($this->path)){} 这个判断
 * 
 */

 /* 
*request标示监听请求的类型 , 
* 回调函数里面 $request 表示请求的内容
*            $response 表示响应的内容
*/ 
$http->on('request', function ($request, $response) use ($http){

    // if(!empty($_GET)){
    //    unset($_GET);
    // }
 
    //转换php 所需的超全局变量 ,在 tp5控制器中,可以直接 用 $_GET 输出这里获取的内容
    if(isset($request->get)){
       foreach($request->get as $k=>$v){
          //获取的参数传给tp框架
          $_GET[$k] = $v;
       }
    }
 
    if(isset($request->header)){
       foreach($request->header as $k=>$v){
          $_SERVER[strtoupper($k)] = $v;
       }
    }
 
    if(isset($request->post)){
       foreach($request->post as $k=>$v){
          $_POST[$k] = $v;
       }
    }
    ob_start();
    //执行应用并响应
    try{
       think\Container::get('app')->run()->send();
    }catch(\Exception $e){
       echo $e->getMessage();
    }   
    $res = ob_get_contents();
    ob_end_clean();
    $http->close();
    $response->end($res);

 });


