<?php

//获取本月开始的时间戳
$begin = mktime(0,0,0,date('m'),1,date('Y'));
//获取本月结束的时间戳
$end = mktime(23,59,59,date('m'),date('t'),date('Y'));

//获取今日14.00的时间戳
$to_start = strtotime(date('Y-m-d') . '14:00:00');

 //获取当天14：00 到
        
 $begin = strtotime(date('Y-m-d'). ' 14:00:00');
 //第二天7：00时间段 需要计算的订单
 $end = strtotime(date('Y-m-d',strtotime('+1 days')). ' 7:00:00');



/**
* 根据时间戳返回星期几
* @param string $time 时间戳
* @return 星期几
*/

function weekday($time){

    if(is_numeric($time)){

        //$weekday = array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
        $weekday = array("7","1","2","3","4","5","6");
        return $weekday[date("w", $time)];
    }

    return false;

}

/**
 * 根据时间戳判断当前月份的天数
 * @param string $time 时间戳
 * @return 当月的天数
 */
function monthdays($time){

    if(is_numeric($time)){

        $monthdays=date("t", $time); //通过date 函数输出时间，定义格式为t
        return $monthdays;

    }

}