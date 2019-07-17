<?php
<?php

//实例化redis

$redis = new Redis();

//连接

$redis->connect('127.0.0.1', 6379);

//字典

//给hash表中某个key设置value

//如果没有则设置成功,返回1,如果存在会替换原有的值,返回0,失败返回0

echo $redis->hset('hash', 'cat', 'cat');echo '<br>';   // 1

echo $redis->hset('hash', 'cat', 'cat');echo '<br>';   // 0

echo $redis->hset('hash', 'cat', 'cat1');echo '<br>';   // 0

echo $redis->hset('hash', 'dog', 'dog');echo '<br>';   // 1

echo $redis->hset('hash', 'bird', 'bird');echo '<br>';   // 1

echo $redis->hset('hash', 'monkey', 'monkey');echo '<br>';   // 1

//获取hash中某个key的值

echo $redis->hget('hash', 'cat');echo '<br>';  // cat1

//获取hash中所有的keys

$arr = $redis->hkeys('hash');

print_r($arr);echo '<br>';

// Array ( [0] => cat [1] => dog [2] => bird [3] => monkey )

//获取hash中所有的值 顺序是随机的

$arr = $redis->hvals('hash');

print_r($arr);echo '<br>';

 // Array ( [0] => cat1 [1] => dog [2] => bird [3] => monkey )

//获取一个hash中所有的key和value 顺序是随机的

$arr = $redis->hgetall('hash');

print_r($arr);echo '<br>';

 // Array ( [cat] => cat1 [dog] => dog [bird] => bird [monkey] => monkey )

//获取hash中key的数量

echo $redis->hlen('hash');echo '<br>';

 // 4

//删除hash中一个key 如果表不存在或key不存在则返回false

echo $redis->hdel('hash', 'dog');echo '<br>';

var_dump($redis->hdel('hash', 'rabbit'));echo '<br>';

// 1

// int(0)
?>