<?php

/** 
 *  liunx + nginx + PHP + MYSQL + Redis
 */

 /** CDN,智能DNS(内容分发网络)
  *  分布式缓存,全国多节点
  *  多线路接入
  *  比如:在深圳部署了一个cdn节点, 
  *        1.深圳客户端会优先从近的地区(深圳)获取资源
  *        2.如果没有获取到资源,就去去源站获取,然后在缓存起来
  */


  /**负载均衡LVS(linux虚拟服务器)  https://www.cnblogs.com/yanjieli/p/10582324.html
   *  大型Web集群
   *   高效稳定(运行在网络层,处理网络链接效率更高,支持更大规模的网络请求)
   * LVS 是一个实现负载均衡集群的开源软件项目，LVS架构从逻辑上可分为调度层、Server集群层和共享存储。
   */