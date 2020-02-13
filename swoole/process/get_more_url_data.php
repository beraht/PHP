<?php

/**
 * url 地址
 */
$urls = [
    "https://www.baidu.com",
    "https://www.sina.com.cn",
    "http://www.sohu.com",
    "https://www.qq.com",
    "https://www.163.com",
    "https://www.cnki.net",
    "https://www.jd.com",
    "https://gl.58.com",
    "https://www.taobao.com",
    "https://qiang.suning.com"
];

//保存每一个子进程的信息
$workers = [];

//程序开始时间
echo "process-start-time".date("Ymd H:i:s"). PHP_EOL;


/**
 * 老方案
 */
// foreach($urls as $url){
//     $content[$url] = file_get_contents($url); 
// }

/**获取多个url地址的内容
 * 老方案: 开启一个主进程,顺序去获取每一个url的内容,执行效率慢
 * 新方案: 开启N个进程,去获取
 */
for($i = 0;$i < 10;$i++){    
    $process = new swoole_process(function(swoole_process $pro) use($i,$urls){
        //todo
        $content = curlData($urls[$i]);
       // echo $content.PHP_EOL;
       //数据写入管道
       $pro->write($content . PHP_EOL);

    }, true);//第二个参数为true,所有的内容都会输入到管道当中
    //开启(启动)一个子进程,返回子进程的ID
    $pid = $process->start();
    echo "子进程的id: " . $pid . PHP_EOL;
    $workers[$pid] =  $process;
    //结束时,相当于回收一个已经结束运行的子进程
   // swoole_process::wait();
}

/**
 * 从管道获取内容(swoole_process对象的第二个参数是true)
 */
foreach($workers as $process){
    //输出管道内容
    echo $process->read() . " ". PHP_EOL;
}


/**
 * 模拟请求url的内容
 * @param $url
 * @return string
 */
function curlData($url){
    //file_get_contents($url); 
    sleep(1);
    return $url."------success------".PHP_EOL;
}

//程序结束时间
echo "process-end-time".date("Ymd H:i:s"). PHP_EOL;

//结束时,相当于回收一个已经结束运行的子进程
    swoole_process::wait();

