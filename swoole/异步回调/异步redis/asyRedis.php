<?php
/**使用的前提
 *  开启redis服务(连接远程的redis也可以)
 *  hiredis库
 *  编译swoole 需要加入 -enable-async-redis
 */

 $redisClient = new swoole_redis;
 $redisClient->connect('127.0.0.1',6379,function(swoole_redis $redisClient,$result){
    echo "coonect";
    if($result){
        echo "连接成功";
    }
    //redis set  key  value
    $redisClient->set("name",'xiaojie',function(swoole_redis $redisClient,$result){
        if($result){
            echo "添加成功";
        }
    });
    //获取
    $redisClient->get("name",function(swoole_redis $redisClient,$result){
        echo $result; //xiaojie
        //关闭
        $redisClient->close();
    });
    
    //获取所有的列表
    $redisClient->keys("*",function(swoole_redis $redisClient,$result){
        echo $result; //xiaojie
        //关闭
        $redisClient->close();
    });

 });   
 





?>