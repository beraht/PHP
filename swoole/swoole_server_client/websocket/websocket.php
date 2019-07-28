<?php
/**
* websocket协议是基于TCP的一种新的网络协议.它实现了浏览器和服务器全双工(full-duplex)通信-----也就是说允许服务器主动发送消息给客户端.
* 建立在TCP协议之上
* 性能开销小通信高
* 客户端可以与任意服务器通信
* 协议标识符 ws wss
* 持久化网络通信协议(长链接)
*
*/

//创建一个对象,
$server = new Swoole\WebSocket\Server("0.0.0.0", 8812);

/**
 * 设置允许的参数
 */
//$server->set([]);


/** 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数。
 *  需要传入二个参数,第一个是 new Swoole\WebSocket\Server 的实例 也就是$server
 *                 第二个参数是一个Http请求对象，包含了客户端发来的握手请求信息 
 *  
 */

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    //打印出请求的客户端唯一标示
    echo "server: handshake success with fd{$request->fd}\n";
});


//另一种写法
///$server->on('open','onOpenfun');
//function onOpenfun($server, $request){
//    print_r($request->fd);
//}





/**当服务器收到来自客户端的数据帧时会回调此函数。(监听ws消息事件)
 *  需要携带二个参数: 第一个是new Swoole\WebSocket\Server 的实例 也就是$server
 *                  第二个是$frame 是swoole_websocket_frame对象，包含了客户端发来的数据帧信息
 */
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    //发送数据给客户端
    $server->push($frame->fd, "this is server");
});

//关闭回调函数
$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();




?>