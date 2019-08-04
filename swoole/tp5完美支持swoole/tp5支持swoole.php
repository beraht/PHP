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
 */


