<?php
//1 . 将所有连接的用户,全部存在redis 里面的集合中
//2. 放在内存table表中,加入时,进入redis ,退出时 删除
class WS{

    /***********************************第一种方法 存在redis************************************** */

    public function __construct() {

        /**********清除reids中所有的fd数据************** */

        ///
        ///
        ///

        $this->ws = new swoole_websocket_server(self::HOST, self::PORT);
        //设置参数
        $this->ws->set(
            [
                'enable_static_handler' => true, //开启静态文件处理
                'document_root' => "/home/tp5/public/static", //静态文件存放的目录
                'worker_num' => 3, //开启服务时,直接开启了5个worke进程,随后进入WorkerStart事件
                'task_worker_num' => 3,
            ]
        );
        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
        $this->ws->on("WorkerStart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        $this->ws->start();
    }
    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request) {
        //记录下连接的用户
        app\common\redis\Predis::getInstance()->sadd("live_open_fd",$request->fd);
        echo $request->fd . '用户进入' .PHP_EOL;
    }


    /**
     * close
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd) {
        //删除关闭链接的用户
        app\common\redis\Predis::getInstance()->srem("live_open_fd",$fd);
        echo $fd . '用户退出' .PHP_EOL;
     }






}