<?php
namespace app\api\controller;
use wechat\mp\Wechat;
use think\Db;
use app\common\controller\Api;

class Wxsend extends Api{
    protected $noNeedLogin = ['*'];

    protected $token = '';

        /**
     * pushMessage 发送自定义的模板消息
     * @param  array  $data          模板数据
        $data = [
            'openid' => '', 用户openid
            'url' => '', 跳转链接
            'template_id' => '', 模板id
            'data' => [ // 消息模板数据
                'first'    => ['value' => urlencode('黄旭辉'),'color' => "#743A3A"],
                'keyword1' => ['value' => urlencode('男'),'color'=>'blue'],
                'keyword2' => ['value' => urlencode('1993-10-23'),'color' => 'blue'],
                'remark'   => ['value' => urlencode('我的模板'),'color' => '#743A3A']
            ]
        ];
     * @param  string $topcolor 模板内容字体颜色，不填默认为黑色
     * @return array
     */
    public function pushMessage($data = [],$topcolor = '#0000'){
        $template = [
            'touser'      => $data['openid'],
            'template_id' => $data['template_id'],
            'url'         => $data['url'],
            'appid'       => config('wx.appid'),
            'topcolor'    => $topcolor,
            "miniprogram" =>'pages/ConnetWifi',
            'pagepath'    => 'pages/ConnetWifi',
            'data'        => $data['data']
        ];
        $json_template = json_encode($template);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->token;
        $result = self::curlPost($url, urldecode($json_template));
        $resultData = json_decode($result, true);
        return $resultData;
    }

    /**
     * 发送post请求
     * @param string $url 链接
     * @param string $data 数据
     * @return bool|mixed
     */
    private static function curlPost($url, $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if(!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * addLog 日志记录
     * @param string $log_content 日志内容
     */
    public static function addLog($log_content = ''){
        $data = "";
        $data .= "DATE: [ " . date('Y-m-d H:i:s') . " ]\r\n";
        $data .= "INFO: " . $log_content . "\r\n\r\n";
        file_put_contents('/wechat.log', $data, FILE_APPEND);
    }

    public function send(){
        $appid = 'wx570a9a99bddf1780'; 
        $secret = '8841fb4e99d5d163ea5ef6650c45edff'; 
        // 获取微信用户信息
        $wechat = new Wechat($appid, $secret);
        $this->token = $wechat->getAccessToken();//获取token
        //获取open_id
        // $user = db('user')->where('group_id','in',['2,3,4,5,6'])->select();
        // halt($user);
        $openid = "oEW1WuFpqsuz8Qx1fMM_RisTdofY";
        //$info = $wechat->get_user_base_info($openid,$this->token);//获取用户信息
        # 公众号消息推送
        $res = $this->pushMessage([
            'openid' => $openid, // 用户openid
            'access_token' => $this->token,
            'template_id' => "Y4Oxf6je2-eK2o0SqTJ0laMAGOUjINZ6KMRh9_VExGM", // 填写你自己的消息模板ID
            'data' => [ // 模板消息内容，根据模板详情进行设置
                'first'    => ['value' => urlencode("有一笔新的订单,请尽快处理"),'color' => "#743A3A"],
                'keyword1' => ['value' => urlencode("2476.00元"),'color'=>'blue'],
                'keyword2' => ['value' => urlencode("13期"),'color'=>'blue'],
                'keyword3' => ['value' => urlencode("15636.56元"),'color' => 'green'],
                'keyword4' => ['value' => urlencode("6789.23元"),'color' => 'green'],
                'remark'   => ['value' => urlencode("更多贷款详情，请点击页面进行实时查询。"),'color' => '#743A3A']
            ],
            'url' => '/pages/ConnetWifis', // 消息跳转链接
        ]);

        halt($res);

    }
}


