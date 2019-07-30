<?php
/**
 * ws 优化 基础类库
 * User: singwa
 * Date: 18/3/2
 * Time: 上午12:34
 */

/**
 * task 任务使用场景: 发送邮件,广播等一些异步比较耗时的任务
 *  原理就是将 异步任务 放进了 swoole_task_work 的进程池中执行,不会影响请求的处理速度.
 *  投递一个异步任务到task_worker池中。此函数是非阻塞的，执行完毕会立即返回。Worker进程可以继续处理新的请求。
 *  使用Task功能，必须先设置 task_worker_num，并且必须设置Server的onTask和onFinish事件回调函数
 *  
 *  适用于任何的服务进程
 * 
 */

class Ws {

    CONST HOST = "0.0.0.0";
    CONST PORT = 8812;

    public $ws = null;
    public function __construct() {
        $this->ws = new swoole_websocket_server("0.0.0.0", 8812);
        //设置参数
        $this->ws->set(
            [
                'worker_num' => 2,
                'task_worker_num' => 2,
            ]
        );
        $this->ws->on("open", [$this, 'onOpen']);
        $this->ws->on("message", [$this, 'onMessage']);
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
        var_dump($request->fd);
        if($request->fd == 1) {
            // 每2秒执行
            swoole_timer_tick(2000, function($timer_id){
                echo "2s: timerId:{$timer_id}\n";
            });
        }
    }

     /**
    * onMessage 接收到客户端发来的信息
    * 
    * 投递一个任务给 task进程池,不会影响当前的运行速度,就说说不会等待task执行完
    * 
    */
    public function onMessage($ws, $frame) {
        echo "ser-push-message:{$frame->data}\n";
        // todo 10s
        $data = [
            'task' => 1,
            'fd' => $frame->fd,
        ];
        $ws->task($data);
        //下面的任务不会等待task执行完,会直接运行

        $ws->push($frame->fd, "server-push:".date("Y-m-d H:i:s"));
    }

    /** 监听 并 接收投递的任务,
     * @param $serv  当前的服务对象
     * @param $taskId  
     * @param $workerId 
     * @param $data  投递过来的数据
     */
    public function onTask($serv, $taskId, $workerId, $data) {
        print_r($data);
        // 耗时场景 10s
        sleep(10);
        return "on task finish"; // 告诉worker
    }

    /** 监听 onTask 并接收 返回的数据 
     * @param $serv
     * @param $taskId
     * @param $data   onTask 返回的数据
     */
    public function onFinish($serv, $taskId, $data) {
        echo "taskId:{$taskId}\n";
        echo "finish-data-sucess:{$data}\n";
    }

    /**
     * close
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd) {
        echo "clientid:{$fd}\n";
    }
}

$obj = new Ws();