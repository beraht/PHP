<?php
namespace app\index\controller;
use app\common\aliyun\Sms;
use app\common\Util;
class Index
{
    public function index()
    {
        print_r($_GET);
        return  "index/index/index" .'-'. time();
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

    /**
     * 发送短信
     */
    public function sms(){
        //$phone = request()->get('phone_num',0,'intval');
        $phone = intval($_GET['phone_num']);
        if(!preg_match("/^1[345678]{1}\d{9}$/",$phone)){
           return Util::show(config('code.error'),'请输入正确的手机号');
        }

        //生成随机字符串
        $code = mt_rand(100000,999999);

        try{

           $res = Sms::sendSms($phone,$code);

        }catch(\Exception $e){

            return Util::show(config('code.sms_error'),'短信发送失败');

        }
        
        if($res->Code === "ok"){

            $redis = new Swoole\Coroutine\Redis();
            $redis->connect(config('redis.host'),config('redis.port'));
            $redis->set("sms_".$phone,$code,config('redis.out_time'));
            return Util::show(config('code.success'),'短信发送成功');

        }else{
            return Util::show(config('code.sms_error'),'短信发送失败');
        }

    }
}
