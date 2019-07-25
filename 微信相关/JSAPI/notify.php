<?php
//还有就是，微信要求支付后处理微信发送的回调内容，就是告诉商户，订单交易成功了，你要发送‘我知道了’给微信。
//还有一点就是：这里就是回调url，你预支付填写的notify_url地址。废话不多说，看下面
 
class pay{

    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_array($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    /* 微信支付完成，回调地址url方法  xiao_notify_url() */
    public function xiao_notify_url(){
        $post = post_data();    //接受POST数据XML个数
        //获取微信官方数据流
        function post_data(){
            $receipt = $_REQUEST;
            if($receipt==null){
                $receipt = file_get_contents("php://input");
                if($receipt == null){
                    $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
                }
            }
            return $receipt;
        }

        //微信支付成功，返回回调地址url的数据：XML转数组Array
        $post_data = $this->xml_to_array($post);   
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        
        /* 微信官方提醒：
         *  商户系统对于支付结果通知的内容一定要做【签名验证】,
         *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
         *  防止数据泄漏导致出现“假通知”，造成资金损失。
         */
        ksort($post_data);// 对数据进行排序
        $str = $this->ToUrlParams($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($post_data));   //再次生成签名，与$postSign比较
        
        $where['crsNo'] = $post_data['out_trade_no'];
        $order_status = M('home_order','xxf_witkey_')->where($where)->find();
        
        if($post_data['return_code']=='SUCCESS'&&$postSign){
            /*
            * 首先判断，订单是否已经更新为ok，因为微信会总共发送8次回调确认
            * 其次，订单已经为ok的，直接返回SUCCESS
            * 最后，订单没有为ok的，更新状态为ok，返回SUCCESS
            */
            if($order_status['order_status']=='ok'){
                //给微信发送确认订单金额和签名正确
                $this->return_success();
            }else{
                $updata['order_status'] = 'ok';
                if(M('home_order','xxf_witkey_')->where($where)->save($updata)){
                    //给微信发送确认订单金额和签名正确
                    $this->return_success();
                }
            }
        }else{
            echo '微信支付失败';
        }
    }
    
    /*
     * 给微信发送确认订单金额和签名正确，SUCCESS信息 -xzz0521
     */
    private function return_success(){
        $return['return_code'] = 'SUCCESS';
        $return['return_msg'] = 'OK';
        $xml_post = '<xml>
                    <return_code>'.$return['return_code'].'</return_code>
                    <return_msg>'.$return['return_msg'].'</return_msg>
                    </xml>';
        echo $xml_post;exit;
    }
}    