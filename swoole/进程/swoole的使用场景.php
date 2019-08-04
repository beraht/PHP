<?php
/**
 * 执行多个url地址,获取里面的内容
 * 
 * 引入swoole process
 * 按需开启N个子进程执行
 * 
 */

 //存放子进程的ID
$workers = [];

$urls = [
    'www.baidu.com',
    'www.qq.com',
    'www.taobao.com',
    'www.hao123.com',
    'www.jd.com',
];

for($i = 0; $i < 5; $i++){
    //开启子进程.第二个参数为true时,有输出时,不会输出到屏幕,而是输出到管道内.
   $process  = new swoole_process(function(swoole_process $pro) use($i,$urls) {

    //curl
    $result  = curlData($urls[$i]);
    //true时 输出到管道内
    echo $result.PHP_EOL;
    //还有一种输出到管道的方法
    //$pro->write($result.PHP_EOL);

    },true);

    $pid = $process->start();
    $workers[$pid] = $process;
}

//获取管道内的内容,之前为true时,输出进去的
foreach($workers as $process){
   echo $process->read();
}


function curlData($url){
    //curl请求模拟
    sleep(1);
    return $url."success";
}



?>