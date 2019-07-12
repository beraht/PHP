<?php
class pay{

    protected  $insertID = 0;
    protected $data_total= 0;
    //签名算法
    protected $sign = '';
    /**生成订单,入库
    * @param  string oppenid 
    * @param  int money
    * @param  int user_id 用户的id(也可以后台自己获取) 
    * @param  string mark 支付的详细(比如会员充值,购买某件商品)
    */
    public function make_order(){
        if(request()->isPost() ){
            $data['openid'] = input('post.openid');
            $data_total = input('post.data_total/d');
            $user_id = input('post.user_id/d');
            $mark = input('post.mark');
            //生成订单号(商户订单号,随机唯一)
            $data['order_id'] = 'W'.date('YmdHis',time()).'-'.randomkeys(2);
            $data['time'] = time();
            $data['status'] = 0;
            $data['user_id'] = $user_id;
            $data['mark'] = $mark;
            //入库
            $insertId = db('Order')->insertGetId($data);
            if($insertId){
                $this->insertID = $insertId;
                $this->data_total = $data_total*100;    //订单总金额，单位分
                /* 调用微信【统一下单】 */
                $this->pay($data_total*100,$data['openid'],$data['crsNo'],$mark);
            }else{
                return show(0,'支付失败,请稍后再试','');
            }
        }else{
            return show(0,'请求参数有误','');
        }
    }
    
/* 首先在服务器端调用微信【统一下单】接口，返回prepay_id和sign签名等信息给前端，前端调用微信支付接口 */
    private function Pay($total_fee,$openid,$order_id,$mark){
        if(empty($total_fee)){
            echo json_encode(array('state'=>0,'Msg'=>'金额有误'));exit;
        }
        if(empty($openid)){
            echo json_encode(array('state'=>0,'Msg'=>'登录失效，请重新登录(openid参数有误)'));exit;
        }
        if(empty($order_id)){
            echo json_encode(array('state'=>0,'Msg'=>'自定义订单有误'));exit;
        }
        $appid =        '小程序appid';//如果是公众号 就是公众号的appid;小程序就是小程序的appid
        $body =         $mark;   //描述 , 如果后期出现问题,可以调试改为英文.
        $mch_id =       '商户账号';
        $KEY = '你申请微信支付的key';
        $nonce_str =    randomkeys(32);//随机字符串 randomkeys()是一个共同方法
        $notify_url =   'https://m.******.com//Home/Xiaoxxf/xiao_notify_url';  //支付完成回调地址url,不能带参数
        $out_trade_no = $order_id;//商户订单号
        $spbill_create_ip = $_SERVER['SERVER_ADDR'];
        $trade_type = 'JSAPI';//交易类型 默认JSAPI
    
        //这里是按照顺序的 因为下面的签名是按照(字典序)顺序 排序错误 肯定出错
        $post['appid'] = $appid;
        $post['body'] = $body;
        $post['mch_id'] = $mch_id; //商户账号
        $post['nonce_str'] = $nonce_str;//随机字符串
        $post['notify_url'] = $notify_url; //回调地址
        $post['openid'] = $openid; //openid
        $post['out_trade_no'] = $out_trade_no; //商户订单号(自定义)
        $post['spbill_create_ip'] = $spbill_create_ip;//服务器终端的ip
        $post['total_fee'] = intval($total_fee);        //总金额 最低为一分钱 必须是整数
        $post['trade_type'] = $trade_type;  //支付的类型
        //生成签名算法(官方)
        $sign = $this->MakeSign($post,$KEY);              //签名
        $this->sign = $sign;
        //组合成 xml格式(官方要求,否则无法识别)
        $post_xml = '<xml>
               <appid>'.$appid.'</appid>
               <body>'.$body.'</body>
               <mch_id>'.$mch_id.'</mch_id>
               <nonce_str>'.$nonce_str.'</nonce_str>
               <notify_url>'.$notify_url.'</notify_url>
               <openid>'.$openid.'</openid>
               <out_trade_no>'.$out_trade_no.'</out_trade_no>
               <spbill_create_ip>'.$spbill_create_ip.'</spbill_create_ip>
               <total_fee>'.$total_fee.'</total_fee>
               <trade_type>'.$trade_type.'</trade_type>
               <sign>'.$sign.'</sign>
            </xml> ';
    
        //统一下单接口prepay_id
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        //curl 调用统一下单api,返回的是一个xml格式
        $xml = $this->http_request($url,$post_xml);     //POST方式请求http
        $array = $this->xml2array($xml);               //将【统一下单】api返回xml数据转换成数组，全要大写
        //转化为数组后,判断返回值
        if($array['RETURN_CODE'] == 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
            //将prepay_id(预支付交易会话标识) 更新入库
            $prepay_id =  str_str( $array['prepay_id']);//处理得到prepay_id;
            db('Order')->where('id',$this->insertID)->update(['prepay_id'=>$prepay_id]);

            //将得到的参数返回给前台
            $time = time();
            $tmp='';                            //临时数组用于签名
            $tmp['appId'] = $appid;
            $tmp['nonceStr'] = $nonce_str;
            $tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
            $tmp['signType'] = 'MD5';
            $tmp['timeStamp'] = $time;
    
            $data['state'] = 1;
            $data['timeStamp'] = $time;           //时间戳
            $data['nonceStr'] = $nonce_str;         //随机字符串
            $data['signType'] = 'MD5';              //签名算法，暂支持 MD5
            $data['package'] = 'prepay_id='.$array['PREPAY_ID'];   //统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=*
            $data['paySign'] = $this->MakeSign($tmp,$KEY);       //签名,具体签名方案参见微信公众号支付帮助文档;
            $data['out_trade_no'] = $out_trade_no;
    
        }else{
            $data['state'] = 0;
            $data['text'] = "错误";
            $data['RETURN_CODE'] = $array['RETURN_CODE'];
            $data['RETURN_MSG'] = $array['RETURN_MSG'];
        }
        echo json_encode($data);
    }
    
    /**
     * 生成签名, $KEY就是支付key
     * @return 签名
     */
    public function MakeSign( $params,$KEY){
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);  //参数进行拼接key=value&k=v
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * 将参数拼接为url: key=value&key=value
     * @param $params
     * @return string
     */
    public function ToUrlParams( $params ){
        $string = '';
        if( !empty($params) ){
            $array = array();
            foreach( $params as $key => $value ){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }
    /**
     * 调用接口， $data是数组参数
     * @return 签名
     */
    public function http_request($url,$data = null,$headers=array())
    {
        $curl = curl_init();
        if( count($headers) >= 1 ){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
    
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    //获取xml里面数据，转换成array
    private function xml2array($xml){
        $p = xml_parser_create();
        xml_parse_into_struct($p, $xml, $vals, $index);
        xml_parser_free($p);
        $data = "";
        foreach ($index as $key=>$value) {
            if($key == 'xml' || $key == 'XML') continue;
            $tag = $vals[$value[0]]['tag'];
            $value = $vals[$value[0]]['value'];
            $data[$tag] = $value;
        }
        return $data;
    }
    



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

}    