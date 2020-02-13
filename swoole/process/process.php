<?php


/**创建一个对象
 * @param 子进程创建成功后要执行的函数
 * @param 重定向子进程的标准输入和输出,启用此选项后，在子进程内输出内容将不是打印屏幕，而是写入到主进程管道
 * @param 管道类型，启用$redirect_stdin_stdout后，此选项将忽略用户参数，强制为1。如果子进程内没有进程间通信，可以设置为 0
 * @param 开启后可以直接在子进程的函数中使用协程API
 */
$process = new swoole_process('callback_function', false);

//开启(启动)一个子进程,返回子进程的ID
$pid = $process->start();
echo "子进程的id: " . $pid . PHP_EOL;

function callback_function(swoole_process $pro)
{
    //在子进程中开启一个http服务,执行一个外部程序  //类似于cli中 php http_server.php
    //这里相当于在子进程(master)中,再次,开启一个manager进程,用作资源管理和分配
    $pro->exec('/usr/local/php/bin/php', array(__DIR__.'/../Create_Server/HTTP/http_server.php'));
}

//结束时,相当于回收一个已经结束运行的子进程
swoole_process::wait();


//查看进程之间的关系  pstree -p 342