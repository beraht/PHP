<?php

class Chart{

    public function index(){
        /**
         * 获取指定端口的链接客户端(比如链接8812 这一个端口的客户端)
         */
        foreach($_POST['http_obj']->ports[1]->connections as $fd){
            $_POST['http_obj']->push($fd,"向链接8812端口的客户端发送数据");
        }

    }

    
}