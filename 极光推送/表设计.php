<?php


/**
 * 注意
 * registrationID和tags必须保存到数据库!因为registrationID是设备号id,每一次登录的时候要更新一下数据库的registrationID!tags是标签,每次给用户立标签的时候都得在数据库更新一遍标签! 对于那个别名,最好是用用户的ID,我这边是,如果你想自己立也可以!! 不过用户的ID也是唯一的,所以用用户的id的话就不用给这个别名建立一个字段了

*iOS需要走苹果的APNS服务，所以需要开发者账号，安卓是TCP长连接
 */



CREATE TABLE `v1_jiguang_user_data` (
  `jg_id` int(10) NOT NULL AUTO_INCREMENT,
  `jiguang_id` varchar(200) CHARACTER SET utf8 NOT NULL,
  `user_id` int(10) NOT NULL,
  `app_type` varchar(200) CHARACTER SET utf8 NOT NULL,
  `is_sent` int(2) NOT NULL DEFAULT '1' COMMENT '判定是都接受app推送，默认1是推送 2是不推送',
  `time` int(11) NOT NULL COMMENT '登录时间',
  `user_sex` int(2) DEFAULT NULL COMMENT '性别 1为男 0为女',
  `longitude` varchar(30) CHARACTER SET utf8 DEFAULT NULL COMMENT '经度',
  `latitude` varchar(30) CHARACTER SET utf8 DEFAULT NULL COMMENT '纬度',
  `region_tag` varchar(40) CHARACTER SET utf8 DEFAULT NULL COMMENT '地区tag',
  PRIMARY KEY (`jg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/*
  $receiver="registration_id" : [ "4312kjklfds2", "8914afd2", "45fdsa31" ];
  $receiver="tag" : [ "深圳", "广州", "北京" ]
  $receiver=  "tag" : [ "深圳", "广州" ]
  $receiver= "tag_and" : [ "女", "会员"]
  //自定义类型推送类型

  $m_type = 'http';//推送附加字段的类型
  $m_txt = 'http://www.groex.cn/';//推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
  $m_time = '86400';//离线保留时间
 *
 * { "platform" : "all" }
 *
 * { "platform" : ["android", "ios"] }
 *  测试成不成功记得看管理平台上面的统计信息，因为之前把apns_production参数设置成了生产环境，调用接口成功，可是却没有用户，后来改为了开发环境，就成功了。
 */

function jiguang_send($receive, $content, $platform, $m_type, $m_txt, $m_time) {

    $appkey = ''; //AppKey
    $secret = ''; //Secret

    $postUrl = "https://api.jpush.cn/v3/push";

    $base64 = base64_encode("$appkey:$secret");
    $header = array("Authorization:Basic $base64", "Content-Type:application/json");
    $data = array();
    $data['platform'] = $platform;          //目标用户终端手机的平台类型android,ios,winphone
    $data['audience'] = $receive;      //目标用户

    $data['notification'] = array(
        //统一的模式--标准模式
        "alert" => $content,
        //安卓自定义
        "android" => array(
            "alert" => $content,
            "title" => "",
            "builder_id" => 1,
            "extras" => array("type" => $m_type, "txt" => $m_txt)
        ),
        //ios的自定义
        "ios" => array(
            "alert" => $content,
            "badge" => "1",
            "sound" => "default",
            "extras" => array("type" => $m_type, "txt" => $m_txt)
        )
    );

    //苹果自定义---为了弹出值方便调测
    $data['message'] = array(
        "msg_content" => $content,
        "extras" => array("type" => $m_type, "txt" => $m_txt)
    );

    //附加选项
    $data['options'] = array(
        "sendno" => time(),
        "time_to_live" => $m_time, //保存离线时间的秒数默认为一天
        "apns_production" => false, //布尔类型   指定 APNS 通知发送环境：0开发环境，1生产环境。或者传递false和true
    );
    $param = json_encode($data);
//    $postUrl = $this->url;
    $curlPost = $param;

    $ch = curl_init();                                      //初始化curl
    curl_setopt($ch, CURLOPT_URL, $postUrl);                 //抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);           // 增加 HTTP Header（头）里的字段
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $return_data = curl_exec($ch);                                 //运行curl
    curl_close($ch);

    return $return_data;
}

因为后面发现极光推送可以自定义分类推送消息，其他我也没看明白，我自己就根据用户的性别，坐标，gps返回的城市自己去查询极光返回的id 
去推送，结果发现对于app开发和php后台开发来说都容易的多

app开发只需要对接基本的sdk就可以了



消息系统设计，这里混合了极光推送的任务表

主表

按 Ctrl+C 复制代码按 Ctrl+C 复制代码

 


从表

CREATE TABLE `v1_push_message_history` (
  `push_message_history_id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL COMMENT '操作用户',
  `content` text CHARACTER SET utf8 COMMENT '推送内容',
  `receiver` text CHARACTER SET utf8 COMMENT 'receiver消息标识',
  `platform` text CHARACTER SET utf8 COMMENT '推送平台',
  `msg_type` int(10) DEFAULT '1' COMMENT '消息类型 1 系统消息  2活动消息 3还款提醒 ',
  `time` int(11) DEFAULT NULL COMMENT '发布时间',
  PRIMARY KEY (`push_message_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


任务表

CREATE TABLE `v1_message_task` (
  `task_id` int(10) NOT NULL AUTO_INCREMENT,
  `task_type` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '任务类型',
  `message_content` varchar(400) CHARACTER SET utf8 NOT NULL COMMENT '信息内容',
  `user_id` int(10) NOT NULL COMMENT '信贷员ID',
  `time` int(11) NOT NULL COMMENT '执行时间',
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
 
然后在crontab里面执行就可以了，查询的时候只需要根据v1_jiguang_user_data的男，女，地区来查询，推送给执行用户，不然就全部发送，直接把
$receiver="registration_id" : [ "4312kjklfds2", "8914afd2", "45fdsa31" ];这样发送就Ok了