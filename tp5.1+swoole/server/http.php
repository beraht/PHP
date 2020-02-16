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

class http {

    CONST HOST = "0.0.0.0";
    CONST PORT = 8811;

    public $http = null;
    public function __construct() {
        $this->http = new Swoole\Http\Server(self::HOST, self::PORT);
        //设置参数
        $this->http->set(
            [
                'enable_static_handler' => true, //开启静态文件处理
                'document_root' => "/home/tp5/public/static", //静态文件存放的目录
                'worker_num' => 5, //开启服务时,直接开启了5个worke进程,随后进入WorkerStart事件
                'task_worker_num' => 5,
            ]
        );

        $this->http->on("WorkerStart", [$this, 'onWorkerStart']);
        $this->http->on("request", [$this, 'onRequest']);
        $this->http->on("task", [$this, 'onTask']);
        $this->http->on("finish", [$this, 'onFinish']);
        $this->http->on("close", [$this, 'onClose']);

        $this->http->start();
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
    
        $_POST = [];
        if (isset($request->post)) {
            foreach ($request->post as $k => $v) {
                $_POST[$k] = $v;
            }
        }
        
        //将服务的对象放入
        $_SERVER['http_obj'] = $this->http;

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
        echo "clientid:{$fd}\n";
    }
}

$obj = new http();