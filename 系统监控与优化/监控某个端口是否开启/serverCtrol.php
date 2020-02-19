<?php
/**
 * 利用swoole监控服务 ws http 8811
 * Created by PhpStorm.
 * Date: 18/4/7
 * Time: 下午10:00
 */

class serverCtrol {
    const PORT = 8811;

    public function port() {
        //php + linux 
        $shell  =  "netstat -anp 2>/dev/null | grep ". self::PORT . " | grep LISTEN | wc -l";

        $result = shell_exec($shell);
        if($result != 1) {
            // 发送报警服务 邮件 短信
            /// todo
            echo date("Ymd H:i:s")."error".PHP_EOL;
        } else {
            echo date("Ymd H:i:s")."succss".PHP_EOL;
        }
    }
}

// nohup
swoole_timer_tick(2000, function($timer_id) {
    (new serverCtrol())->port();
    echo "time-start".PHP_EOL;
});


//使用方法 (执行脚本有输出就打印在log.txt里面,不在cli显示)
//在liunx-cli : nohup /usr/local/php/bin/php    /home/tp5/server/serverCtrol.php  >  /home/tp5/log/log.txt &
//判断这个进程是否启动   ps aux | grep server/serverCtrol.php 