<?php
/**
 * 异步文件读写基于线程池同步IO模拟实现，文件读写请求投递到任务队列，然后由AIO线程读写文件，完成后通知主线程
 */



 /**
  * 读取一个文件
  * 第一个参数为要读取的文件
  * 回调函数: 第一个参数,就是要读取的文件
  *          第二个参数,就是      
  *
  */
 swoole_async_readfile(__DIR__."/1.txt",function($filename,$filecontent){
     //输出了文件名及所在位置
    echo "filename:".$filename.PHP_EOL; 
    //输出文件的内容 
    echo "content:".$filecontent.PHP_EOL;
 });


 /**
  * 读取文件时,返回是否读取成功,返回一个bool值
  * 比如读取的文件不存在,或者读取失败,返回一个false
  */
$result =  swoole_async_readfile(__DIR__."/1.txt",function($filename,$filecontent){
    //输出了文件名及所在位置
   echo "filename:".$filename.PHP_EOL; 
   //输出文件的内容 
   echo "content:".$filecontent.PHP_EOL;
});

//true 或者 false
var_dump($result);


/**
 * 分断读取超大类型的文件
 * 每次只读$size个字节，不会占用太多内存。
 * 
 */
$res = swoole_async_read(__DIR__."/1.txt", function($filename,$filecontent){
            //输出了文件名及所在位置
            echo "filename:".$filename.PHP_EOL; 
            //输出文件的内容 
            echo "content:".$filecontent.PHP_EOL;
},$size = 8192,$offset = 0);


?>