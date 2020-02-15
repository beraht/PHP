<?php
use Swoole\Http\Server;
/**
 * 如何使 tp5.1 完全支持swoole
 * 当访问 : xxx.com/index/index/test 时
 *  1.先触发事件 request , 在进入request回调当中
 *  2.tp的每一个请求首先都会进入到index.php
 * 
 *  3.使用onWorkerStart事件(此事件在Worker进程/Task进程启动时发生。这里创建的对象可以在进程生命周期内使用)
 *      a.可以通过$server->taskworker属性来判断当前是Worker进程还是Task进程
*       b.设置了worker_num和task_worker_num超过1时，每个进程都会触发一次onWorkerStart事件，可通过判断$worker_id区分不同的工作进程

 *  4.为了让tp5完美支持swoole ,需要在每一次启动服务之前,tp的核心代码,加载到swoole进程onWorkerStart事件当中,
 *      a.当后面启动了多个进程,每个进程都已经加载了tp核心代码了.
 *      b.这样做的目的是,应用热加载,放入work进程中就启动了,
 */

$http = new Server("0.0.0.0", 8811);

$http->set(
    [
        'enable_static_handler' => true, //开启静态文件处理
        'document_root' => "/home/tp5/public/static", //静态文件存放的目录
        'worker_num' => 5, //开启服务时,直接开启了5个worke进程,随后进入WorkerStart事件
    ]
);
//如果请求的静态文件,在指定的目录下找到,该文件,那么久不会在走下面的逻辑了.

//此事件在Worker进程启动时发生
$http->on('WorkerStart', function ($serv, $worker_id) {

    echo "启动了一个workem进程: " . $worker_id . PHP_EOL;

    //定义应用目录
    define('APP_PATH',__DIR__.'/../application/');
    // 加载基础文件
    require __DIR__ . '/../thinkphp/base.php';
    // 执行应用并响应(不用去引入,因为只要加载核心库就行了,没必要去执行)
    //Container::get('app')->run()->send();
});

//请求进入
$http->on('request', function ($request, $response)use($http) {
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

});

$http->start();

