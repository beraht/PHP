<?php

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
    CONST PORT = 8811;

    public $ws = null;
    public function __construct() {
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
    * onMessage 接收到客户端发来的信息
    * 
    * 投递一个任务给 task进程池,不会影响当前的运行速度,就说说不会等待task执行完
    * 
    */
    public function onMessage($ws, $frame) {
        echo "接收一个消息:" . "发送者 :" . $frame->fd . " 发送的数据: $frame->data\n";
        // todo 10s
        $data = [
            'task' => 1,
            'fd' => $frame->fd,
        ];
        
    }

    //此事件在Worker进程启动时发生
    public function onWorkerStart($serv, $worker_id){
        echo "启动了一个workem进程: " . $worker_id . PHP_EOL;

        //定义应用目录
        define('APP_PATH',__DIR__.'/../application/');
        // 加载基础文件
        require __DIR__ . '/../thinkphp/base.php';
        // 执行应用并响应(不用去引入,因为只要加载核心库就行了,没必要去执行)
        //Container::get('app')->run()->send();
    }

    //请求进入
    public function onRequest($request, $response){
        $uri = $request->server['request_uri'];
        if ($uri == '/favicon.ico') {
            $response->status(404);
            $response->end();
        }
        $_SERVER = [];
        if (isset($request->server)) {
            foreach ($request->server as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if (isset($request->header)) {
            foreach ($request->header as $k => $v) {
                $_SERVER[strtoupper($k)] = $v;
            }
        }
    
        $_GET = [];
        if (isset($request->get)) {
            foreach ($request->get as $k => $v) {
                $_GET[$k] = $v;
            }
        }
    
        $_FILES = [];
        if (isset($request->files)) {
            foreach ($request->files as $k => $v) {
                $_FILES[$k] = $v;
            }
        }

        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }
        
        //将服务的对象放入
        $_SERVER['http_obj'] = $this->ws;

        //打开输出缓冲区，所有的输出信息不在直接发送到浏览器
        // echo，并不一定会输出东西，而是保存在 buffer 里。
        ob_start();
        try {
            // 执行应用并响应
            \think\Container::get('app',[APP_PATH])->run()->send(); 
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    
        //获取缓冲器的内容
        $res = ob_get_clean();
        //响应给浏览器
        $response->end($res);
    }

    /** 监听 并 接收投递的任务,
     * @param $serv  当前的服务对象
     * @param $taskId  
     * @param $workerId 
     * @param $data  投递过来的数据
     */
    public function onTask($serv, $taskId, $workerId, $data) {

        //分发 task 任务机制 ,让不同的任务, 走不同的逻辑

        $taskObj = new app\common\task\Task();
        $method = $data['method'];
        $res = $taskObj->$method($data['data']);
        return $res; // 告诉worker
        
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
       //删除关闭链接的用户
       app\common\redis\Predis::getInstance()->srem("live_open_fd",$fd);
       echo $fd . '用户退出' .PHP_EOL;
    }
}

$obj = new Ws();