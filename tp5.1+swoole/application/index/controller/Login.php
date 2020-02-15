<?php
namespace app\index\controller;
use app\common\aliyun\Sms;
use app\common\Util;
use app\common\redis\Predis;
class Login
{
    /**
     * 登入逻辑
     */
    public function login()
    {
        $phone = intval($_GET['phone_num']);
        $code = intval($_GET['code']);
        
        if(empty($phone) || empty($code)){

            return Util::show(config('code.error'),'phone or code error');

        }

        //从Redis取出key,对比
        $redis_code = Predis::getInstance()->get("sms_".$phone);

        if($redis_code == $code){
            $data = [
                'phone' => $phone,
                'time'  => time(),
                'is_Login' => true,
            ];

            Predis::getInstance()->set("user_".$phone,$data);

            return Util::show(config('code.success'),'success');

        }else{

            return Util::show(config('code.error'),'phone or code error');

        }

    }

}
