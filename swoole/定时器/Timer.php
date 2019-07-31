<?php
/**
 * 毫秒精度的定时器。 
 */

// 每2秒执行
swoole_timer_tick(2000, function($timer_id){
    echo "2s: timerId:{$timer_id}\n";
});

//每5S向 客户端发送数据,不会影响后面代码的运行.
swoole_timer_after(5000, function() use($ws, $frame) {
    echo "5s-after\n";
    $ws->push($frame->fd, "server-time-after:");
});



?>