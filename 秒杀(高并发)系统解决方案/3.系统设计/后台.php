<?php

/**
 * 活动管理
 */

--
-- Table structure for table `ms_active`
--

DROP TABLE IF EXISTS `ms_active`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ms_active` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `title` varchar(255) NOT NULL COMMENT '活动名称',
  `time_begin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `time_end` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `sys_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `sys_lastmodify` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `sys_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '状态，0 待上线，1 已上线，2 已下线',
  `sys_ip` varchar(50) NOT NULL COMMENT '创建人IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='活动信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ms_active`
--


/**
 * 商品管理
 */
DROP TABLE IF EXISTS `ms_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ms_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `active_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  `title` varchar(255) NOT NULL COMMENT '商品名称',
  `description` text NOT NULL COMMENT '描述信息，文本，要支持HTML',
  `img` varchar(255) NOT NULL COMMENT '小图标，列表中显示',
  `price_normal` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '原价',
  `price_discount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀价',
  `num_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总数量',
  `num_user` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '单个用户限购数量',
  `num_left` int(11) NOT NULL DEFAULT '0' COMMENT '剩余可购买数量',
  `sys_dateline` int(11) NOT NULL DEFAULT '0' COMMENT '信息创建时间',
  `sys_lastmodify` int(11) NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `sys_status` int(11) NOT NULL DEFAULT '0' COMMENT '状态，0 待上线，1 已上线，2 已下线',
  `sys_ip` varchar(50) NOT NULL COMMENT '创建人的IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='商品信息表';


 /**
 * 订单管理
 */
DROP TABLE IF EXISTS `ms_trade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ms_trade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `active_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  `goods_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `num_total` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '购买的单品数量',
  `num_goods` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买的商品种类数量',
  `price_total` decimal(10,0) unsigned NOT NULL DEFAULT '0' COMMENT '订单总金额',
  `price_discount` decimal(10,0) unsigned NOT NULL DEFAULT '0' COMMENT '优惠后实际金额',
  `time_confirm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '确认订单时间',
  `time_pay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `time_over` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  `time_cancel` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '取消时间',
  `goods_info` mediumtext NOT NULL COMMENT '订单商品详情，JSON格式保存',
  `sys_dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `sys_lastmodify` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  `sys_status` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '状态，0 初始状态，1 待支付，2 已支付，3 已过期，4 管理员已确认，5 已取消，6 已删除，7 已发货，8 已收货，9 已完成',
  `sys_ip` varchar(50) NOT NULL COMMENT '用户IP',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `active_id` (`active_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='订单信息表';


/**
 * 日志管理
 */

--
-- Table structure for table `ms_log`
--

DROP TABLE IF EXISTS `ms_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ms_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `active_id` int(10) unsigned NOT NULL COMMENT '活动ID',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID',
  `action` varchar(50) NOT NULL COMMENT '操作名称',
  `result` varchar(50) NOT NULL COMMENT '返回信息',
  `info` text NOT NULL COMMENT '操作详情,JSON格式保存，比如：POST，refer, 浏览器等信息',
  `sys_dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  `sys_lastmodify` int(10) unsigned NOT NULL COMMENT '最后修改时间',
  `sys_status` int(10) unsigned NOT NULL COMMENT '状态，0 正常，1 异常，2 已处理的异常',
  `sys_ip` varchar(50) NOT NULL COMMENT '用户IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='秒杀的详细操作日志';
/*!40101 SET character_set_client = @saved_cs_client */;

