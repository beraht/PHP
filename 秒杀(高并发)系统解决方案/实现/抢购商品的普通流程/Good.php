<?php
namespace app\index\controller;

/**
 *  商品抢购逻辑(秒杀)
 *  
 */ 
class Good extends Controller{

    public function goodPay(){

        $userInfo = $this->auth->getUserinfo();

        if(empty($userInfo))
            $this->error(__('未登录'));
        $platform = empty($_SERVER['HTTP_PLATFORM']) ? '' : $_SERVER['HTTP_PLATFORM'];//ios,android,小程序不传该值

        $user_id = $userInfo['id'];

        $good_id = $this->request->param('good_id/d'); //商品id
        $act_id = $this->request->param('act_id/d'); //活动id
        $good_num = $this->request->param('good_num/d'); //商品数量

        /***** 以下顺序可以根据性能调整 ***** */

        //1.验证参数是否正确合法 (包括)

        //1.验证是否登入
        
        //2.验证参数是否正确,合法

        //3.验证用户是否已经购买 (防止重复提交订单)

        //4.验证 问答系统 的问题是否正确


        //5.验证活动信息(是否结束),商品信息是否正常(商品的状态)

        //6.验证用户购买的商品数量是否在限制范围内

        //7.验证商品 是否还有剩余数量

        //8.扣除商品剩余数量

        //9.创建订单

        //10.返回提示信息


    } 


}