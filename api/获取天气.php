<?php
//百度天气接口API

$location = "北京";  //地区

$ak = "5slgyqGDENN7Sy7pw29IUvrZ"; //秘钥，需要申请，百度为了防止频繁请求

$weatherURL = "http://api.map.baidu.com/telematics/v3/weather?location=$location&output=json&ak=$ak";   

$ch = curl_init($weatherURL) ;

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回

curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回

$result = curl_exec($ch);

echo $result;