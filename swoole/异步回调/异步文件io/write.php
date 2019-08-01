<?php
/**
 * 将内容写入到文件中
 * 参数1为文件的名称，必须有可写权限，文件不存在会自动创建。打开文件失败会立即返回false
 *参数2为要写入到文件的内容，最大可写入4M
 *参数3为写入成功后的回调函数，可选
 *参数4为写入的选项，可以使用FILE_APPEND表示追加到文件末尾
 *如果文件已存在，底层会覆盖旧的文件内容
 */
$content = date("Y-m-d H:i:s");
swoole_async_writefile('test.log', $file_content, function($filename) {
    echo "wirte ok.\n";
}, $flags = 0);

/**
 * 假设将客户端发送过来的数据入库
 */
$http->on('request',function($request,$respone){
    $content = [
        'date' => date('Y-m-d H:i:s'),
        'get' => $request->get,
        'post' => $request->post,
    ];
    swoole_async_writefile('test.log', json_encode($content), function($filename) {
        echo "wirte ok.\n";
    }, FILE_APPEND);
    //设置cookie返回客户端保存
    $respone->cookie('name','jie',time()+1111);

});

?>