<?php
namespace app\admin\controller;
use app\common\Util;
use app\common\redis\Predis;
class Live{

    /**
     * 接收数据并入库,随后推送
     */
    public function push(){
        $data = $_GET;
        print_r($data);

        //获取所有fd
        $fds = Predis::getInstance()->Smembers("live_open_fd");

        //推送给所有的用户
        //方案一(同步)
        // foreach($fds as $fd){
        //     $_SERVER['http_obj']->push($fd,"hello123heloo");
        // }

        //方案二(异步)
        
        //发送一个task任务
        $TaskData = [
            'method' => "pushTack",
            'data'  =>  $fds,
        ];
        
        $_SERVER['http_obj']->task($TaskData);  //相当于 $thhp->task();
        


        return Util::show(1,'ok');
    }




}