//实例化redis

$redis = new Redis();

//连接

$redis->connect('127.0.0.1', 6379);

//集合

// 添加一个元素

echo $redis->sadd('set', 'cat');echo '<br>';         // 1

echo $redis->sadd('set', 'cat');echo '<br>';         // 0

echo $redis->sadd('set', 'dog');echo '<br>';        // 1

echo $redis->sadd('set', 'rabbit');echo '<br>';     // 1

echo $redis->sadd('set', 'bear');echo '<br>';      // 1

echo $redis->sadd('set', 'horse');echo '<br>';    // 1

// 查看集合中所有的元素

$set = $redis->smembers('set');

print_r($set);echo '<br>';

// Array ( [0] => rabbit [1] => cat [2] => bear [3] => dog [4] => horse )

//删除集合中的value

echo $redis->srem('set', 'cat');echo '<br>';    // 1

var_dump($redis->srem('set', 'bird'));echo '<br>';     // int(0)

$set = $redis->smembers('set');

print_r($set);echo '<br>';

// Array ( [0] => dog [1] => rabbit [2] => horse [3] => bear )

//判断元素是否是set的成员

var_dump($redis->sismember('set', 'dog'));echo '<br>';     // bool(true)

var_dump($redis->sismember('set', 'bird'));echo '<br>';    // bool(false)

//查看集合中成员的数量

echo $redis->scard('set');echo '<br>';    // 4

//移除并返回集合中的一个随机元素(返回被移除的元素)

echo $redis->spop('set');echo '<br>';  // bear

print_r($redis->smembers('set'));echo '<br>';   

 // Array ( [0] => dog [1] => rabbit [2] => horse )

<?php

//实例化redis

$redis = new Redis();

//连接

$redis->connect('127.0.0.1', 6379);

//集合

$redis->sadd('set', 'horse');

$redis->sadd('set', 'cat');

$redis->sadd('set', 'dog');

$redis->sadd('set', 'bird');

$redis->sadd('set2', 'fish');

$redis->sadd('set2', 'dog');

$redis->sadd('set2', 'bird');

print_r($redis->smembers('set'));echo '<br>';

 // Array ( [0] => cat [1] => dog [2] => bird [3] => horse )

print_r($redis->smembers('set2'));echo '<br>';

// Array ( [0] => bird [1] => dog [2] => fish )

//返回集合的交集

print_r($redis->sinter('set', 'set2'));echo '<br>';

// Array ( [0] => dog [1] => bird )

//执行交集操作 并结果放到一个集合中

$redis->sinterstore('output', 'set', 'set2');

print_r($redis->smembers('output'));echo '<br>';

// Array ( [0] => dog [1] => bird )

//返回集合的并集

print_r($redis->sunion('set', 'set2'));echo '<br>';

// Array ( [0] => cat [1] => dog [2] => bird [3] => horse [4] => fish )

//执行并集操作 并结果放到一个集合中

$redis->sunionstore('output', 'set', 'set2');

print_r($redis->smembers('output'));echo '<br>';

 // Array ( [0] => cat [1] => dog [2] => bird [3] => horse [4] => fish )

//返回集合的差集

print_r($redis->sdiff('set', 'set2'));echo '<br>';

// Array ( [0] => horse [1] => cat )

//执行差集操作 并结果放到一个集合中

$redis->sdiffstore('output', 'set', 'set2');

print_r($redis->smembers('output'));echo '<br>';

// Array ( [0] => horse [1] => cat )<?php

?>