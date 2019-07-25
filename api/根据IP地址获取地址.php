<?php
/**

 *  调用淘宝API根据IP查询地址

 */

public function ip_address()

{

    $ip = '219.134.104.255';

    $durl = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;

    // 初始化

    $curl = curl_init();

    // 设置url路径

    curl_setopt($curl, CURLOPT_URL, $durl);

    // 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true) ;

    // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回

    curl_setopt($curl, CURLOPT_BINARYTRANSFER, true) ;

    // 执行

    $data = curl_exec($curl);

    // 关闭连接

    curl_close($curl);

    // 返回数据

    return $data;

}