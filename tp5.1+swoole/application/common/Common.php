<?php
namespace app\common;
use think\Controller;
use think\Exception;
use think\facade\Cache;
use app\api\controller\Auth;
/**
 * 公共类
 */
class Common extends Controller{
    /**
     * 初始化的方法
     */
    public function initialize(){
        $this->checkRequestAuth();
    }

    /**
     * 检查每次请求的数据是否合法 (sign 放在header头中请求)
     */
    public function checkRequestAuth(){
        //首先获取header中的信息
        $headers = request()->header();
        if(empty($headers['sign']) ){
            throw new Exception('您没有未授权',401);
            exit();
        }

        $data = [
            'sign' => $headers['sign'],
         ];



        //解密
        $flag = Auth::checkSign($data);

        if(!$flag){
            throw new Exception('您没有未授权,非法登入',401);
            exit();
        }

    }

}