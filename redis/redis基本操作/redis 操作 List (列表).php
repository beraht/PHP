<?php
  //存储数据到列表中

  $redis->lpush('list', 'html');

  $redis->lpush('list', 'css');
  
  $redis->lpush('list', 'php');
  
  //获取列表中所有的值
  
  $list = $redis->lrange('list', 0, -1);
  
  print_r($list);echo '<br>'; 
  
  // Array ( [0] => php [1] => css [2] => html )
  
  //从右侧加入一个
  
  $redis->rpush('list', 'mysql');
  
  $list = $redis->lrange('list', 0, -1);
  
  print_r($list);echo '<br>';
  
  // Array ( [0] => php [1] => css [2] => html [3] => mysql )
  
  //从左侧弹出一个
  
  $redis->lpop('list');
  
  $list = $redis->lrange('list', 0, -1);
  
  print_r($list);echo '<br>';
  
  // Array ( [0] => css [1] => html [2] => mysql )
  
  //从右侧弹出一个
  
  $redis->rpop('list');
  
  $list = $redis->lrange('list', 0, -1);
  
  print_r($list);echo '<br>';
  
  // Array ( [0] => css [1] => html )

?>