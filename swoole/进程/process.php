<?php


/**
 * 进程是正在运行程序的一个实例
 * 查看 
 */

/**
 * 在主进程中 创建一个对象(子进程)
 * 第二个参数为true时,将输出放入了子进程的管道里面去了
*/
 $process = new swoole_process(function(swoole_process $pro){
        //在子进程内执行一个外部的程序 第一个 参数 相对于 php x.php 
                                //第二个参数,表示要执行的文件的路劲          
        $pro->exec("/usr/local/php/bin/php",[__DIR__.'/../server/TCP_Server.php']);


  },false);

  /**
   * 在这里 process.php 相对于一个主进程
   * 在这里 开启一个子进程,成功的话,返回一个子进程的id
   */
  $pid = $process->start();
  echo $pid . PHP_EOL;

  //回收结束运行的子进程。
  $process ::wait();

?>