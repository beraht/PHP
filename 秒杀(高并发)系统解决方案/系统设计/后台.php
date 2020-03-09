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



 /**
 * 订单管理
 */



/**
 * 日志管理
 */

