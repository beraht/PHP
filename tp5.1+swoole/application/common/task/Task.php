<?php
namespace app\common\task;
use app\common\aliyun\Sms;
use app\common\redis\Predis;
use Config;
/**
 * php 操作Redis
 */
class Task{

    /**
     * 异步task发送短信
     */
    public function smsTack($data){

        try{

            $res = Sms::sendSms($data['phone'],$data['code']);
 
         }catch(\Exception $e){
 
            return false;
 
         }

        if($res->Code === "OK"){

            Predis::getInstance()->set("sms_".$data['phone'],$data['code'],500);
            return true;

        }else{

            return false;
        }


    }




}
