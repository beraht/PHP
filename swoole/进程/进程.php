<?php

/**
 * 进程是正在运行程序的一个实例
 */

/**
 * 创建一个对象
*/
 $process = new swoole_process(function(swoole_process $pro){

  },true);

  //开启一个子进程
  $pid = $process->start();


?>