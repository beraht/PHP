<?php
/**
  +----------------------------------------------------------
 * 浏览记录按照时间排序
  +----------------------------------------------------------
 */
function my_sort($a, $b){
$a = substr($a,1);
$b = substr($b,1);
if ($a == $b) return 0;
return ($a > $b) ? -1 : 1;
  }
/**
  +----------------------------------------------------------
 * 网页浏览记录生成
  +----------------------------------------------------------
 */
function cookie_history($id,$title,$price,$img,$url){
$dealinfo['title'] = $title;
$dealinfo['price'] = $price;
$dealinfo['img'] = $img;
$dealinfo['url'] = $url;
$time = 't'.NOW_TIME;
$cookie_history = array($time => json_encode($dealinfo));  //设置cookie
if (!cookie('history')){//cookie空，初始一个
cookie('history',$cookie_history);
}else{
$new_history = array_merge(cookie('history'),$cookie_history);//添加新浏览数据
uksort($new_history, "my_sort");//按照浏览时间排序
$history = array_unique($new_history);
if (count($history) > 4){
$history = array_slice($history,0,4);
}
cookie('history',$history);
}
}
/**
  +----------------------------------------------------------
 * 网页浏览记录读取
  +----------------------------------------------------------
 */
function cookie_history_read(){
$arr = cookie('history');
foreach ((array)$arr as $k => $v){
$list[$k] = json_decode($v,true);
}
return $list;
}