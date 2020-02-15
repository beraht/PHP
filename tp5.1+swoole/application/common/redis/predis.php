<?php
namespace app\common\redis;

/**
 * php 操作Redis
 */
class Predis{

    public $redis = "";

    /**
     * 定义单例模式变量
     */
    private static $_instance = null;

    public static function getInstance(){
        if(empty(self::$_instance)){    
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    private function __construct(){
        $this->redis = new \Redis(); //redis.so在全局路径下
        $result = $this->redis->connect(config('redis.host'),config('redis.port'),config('redis.timeOut'));
        if($result == false){
            throw new \Exception('redis connect error');
        }
    }   

    /**
     * 设置
     */
    public function set($key, $value, $time = 0 ){
        
        if(!$key){
            return '';
        }

        if(is_array($value)){
            $value = json_encode($value);
        }

        if(!$time){
            return $this->redis->set($key,$value);
        }

        return $this->redis->setex($key, $time, $value);

    }

    /**
     * 获取
     */

    public function get($key, $value, $time = 0 ){
        
        if(!$key){
            return '';
        }

        return $this->redis->get($key);

    }


}
