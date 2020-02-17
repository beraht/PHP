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
        foreach($fds as $fd){
            $_SERVER['http_obj']->push($fd,"hello123heloo");
        }
        return Util::show(1,'ok');
    }




}