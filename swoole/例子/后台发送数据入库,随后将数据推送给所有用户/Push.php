<?php

class Live{

    /**
     * 1.接收后台发送的数据
     * 2.接收入库后,将数据发送给前台ws
     */
    public function push(){
        //接收所有数据
        $data = $_POST;
        //入库 

        //处理(组织)好数据,发送给前台

        $_SERVER['http_obj']->push(2,'发送给ws的数据'); //发送给标识为2的客户端

        
    }




}