<?php
// 设置一个字符串的值

$redis->set('cat', 111);

//获取一个字符串的值

echo $redis->get('cat'); // 111

// 重复set

$redis->set('cat', 222);

echo $redis->get('cat'); // 222


?>