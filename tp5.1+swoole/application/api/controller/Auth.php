<?php
namespace app\api\controller;
use app\common\aes\AES;
use think\facade\Cache;
class  Auth{

    /**
     * 生成13位时间戳(防止生成重复的sign)
     */

    public  static function getUnixTimestamp ()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($s1) + floatval($s2)) * 1000);

    }


    /**
     * 生成每次请求的sign算法 
     * @param int user_id
     * @return string aes
     */
    public static function setSign($user_id){
        $data = [
            'user_id' => $user_id,
            'time' => self::getUnixTimestamp()
        ];
        //1.字段排序    
        krsort($data);
        //2.已字符串的形式&拼接起来
        $input = http_build_query($data); 
        //3.通过aes加密
        $aes_str = (new AES())->encrypt($input);
        return $aes_str;

    }

    public function get(){
        return self::setSign(66);
    }

    /**
     * 检查每一次携带的sign是否正确
     * @param array sign等其他请求参数
     * @return boolean
     */
    public  static function checkSign($data){
        if(empty($data)){
            return false;
        }
        //解密
        $aes_str = (new AES())->decrypt($data['sign']);
        if(empty($aes_str)){
            return false;
        }
        //转化为数据
        parse_str($aes_str,$arr);

        //可以选择多重判断
        if(empty($arr)) return false;

        //判断时间戳是否合法 超过60秒后请求不合法
        if(time() - ceil($arr['time'] / 1000 ) > 60){
            return false;
        }

        //sign 唯一性 判断(一个sign只能请求一次)
        //方案: mysql  redis  缓存
        if(Cache::get($data['sign'])){
            return false;
        }
        //sign写入缓存
        Cache::set($data['sign'],1,65);

        return  true;
    }

}