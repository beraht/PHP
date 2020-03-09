<?php

/***
 * 验证码  / 问答
 */
LOCK TABLES `ms_question` WRITE;
/*!40000 ALTER TABLE `ms_question` DISABLE KEYS */;
INSERT INTO `ms_question` VALUES (1,1,'下面的哪个是正确的翻译?','春天','spring','夏天','summer','冬天','winter','秋天','autumn','红色','red','蓝色','blue','黄色','yellow','白色','white','黑色','black','橙色','orange',1500198704,1500199367,0,'127.0.0.1'),(2,2,'下面哪个是正确的省会城市','河北','石家庄','河南','郑州','山西','太原','陕西','西安','甘肃','兰州','江西','南昌','浙江','杭州','广东','广州','江苏','南京','安徽','合肥',1500561787,1500561787,0,'127.0.0.1');
/*!40000 ALTER TABLE `ms_question` ENABLE KEYS */;
UNLOCK TABLES;


/**
 * 防攻击
 */