<?php
/** 操作计算机系统内存
 * swoole_table 是基于共享内存和锁实现的超高性能,并发数据结构,用于解决多进程/多线程数据共享和同步加锁问题
 * 利用swoole可以在内存中生成一个内存模块,用于管理相关的数据表
 * 使用场景: 用于多线程当中,处理数据的共享,比如 :
 *        一个主进程创建了10个子进程,那么主进程和子进程之间,或者子进程和主进程之间共享数据.
 */


 /**
  * 创建内存表
  */
  $table = new swoole_table(1024);

  /**
   * 内存表增加一列  设置字段  设置类型
   */
    $table->column('id',$table::TYPE_INT,4);
    $table->column('name',$table::TYPE_STRING,64);
    $table->column('age',$table::TYPE_INT,3);
    $table->create();

    /**
     * 新增一条数据  key=>value
     */
    $table->set('userinfo' , ['id'=>1,'name'=>'xiaojie','age'=>18] );


    /**
     * 原子操作 curd
     */
     //每次增加 2
     $table->incr('userinfo',"age",2);

    /**
     * 获取数据
     */
    print_r( $table->get('userinfo') );

    /**
     * 当程序结束时,内存表也会消失.
     */




?>