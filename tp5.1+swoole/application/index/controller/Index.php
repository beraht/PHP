<?php
namespace app\index\controller;
use app\common\aliyun\Sms;
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
        $phone = request()->get('phone_num',0,'intval');
        if(empty($phone)){
           return app\common\Util::show(config('code.error'),'请输入正确的手机号');
        }

        try{

           $res = Sms::sendSms($phone,$code);
           print_r($res);
        }catch(\Exception $e){

            $res = $e->getMessage();

        }
            return $res;

    }
}
