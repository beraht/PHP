<?php
namespace app\api\controller;
use wechat\mp\Wechat;
use think\Db;
use app\common\controller\Api;

class Wxsend extends Api{
    protected $noNeedLogin = ['*'];

    protected $token = '';
    protected $appid = '';
    protected $secret = '';


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
            'color'       =>  $topcolor,
            "miniprogram" => [ 'appid'=>config('wx.appid'),'pagepath'=>'/pages/ServiceOrderPage'],
            'data'        => $data['data']
        ];
        
        $json_template = json_encode($template);
       // halt($json_template);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->token;
        $result = self::curlPost($url, urldecode($json_template));
        $resultData = json_decode($result, true);
        return $resultData;
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

    /**
     * 获取所有关注这个公众号的用户信息入库
     */
    public function get_send_data(){
        $appid = config('gzh.appid'); 
        $secret = config('gzh.secret');

        // 获取微信用户信息
        $wechat = new Wechat($appid,$secret);
        //获取token
        $this->token = $wechat->getAccessToken();
        //获取所有用户的open_id(关注了的)
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token=$this->token&next_openid=";
        $openids = $wechat->httpGet($url);
        //获取微信公众号的所有用户信息(获取到 unionid 和 openid )存入数据库
        $open_info = json_decode($openids,true);
        $openid = $open_info['data']['openid'];
        //判断是否存在数据

        $gzh_ids = Db('user_gzh')->field('id')->select();
        $ids = array_column($gzh_ids,'id');
        db('user_gzh')->delete($ids);
        foreach($openid as $id){
          $user_info  = $wechat->get_user_base_info($id,$this->token);

          $data['openid'] = $user_info->openid;
          $data['unionid'] = $user_info->unionid;
          $data['nickname'] = $user_info->nickname;
          $data['createtime'] = time();

          Db('user_gzh')->insert($data); 

        }

    }
        /**
         * @param int  订单id 获取到用户的名称 和 点的菜单 桌号
         */
        public function send($order_id){

            $order_id = $this->request->param('order_id');

            //获取到所有的订单信息
            $oders = db('order_compute')->where('order_id','=',$order_id)->select();
            $goods_order = array_column($oders,'goods_name');

            $goods_name = implode(',',$goods_order);
            //获取到桌号
            $oder = db('sm_order')->where('order_id','=',$order_id)->find();
            $desknum = $oder['desk_num'];
            $user_id = $oder['user_id'];
            $user = db('user')->where('id','=',$user_id)->find();
            $user_name = $user['nickname'];


            $appid = config('gzh.appid'); 
            $secret = config('gzh.secret');

            // 获取微信用户信息
            $wechat = new Wechat($appid,$secret);
            //获取token
            $this->token = $wechat->getAccessToken();

            //获取用户表指定的用户(店内人员)
            $user = db('user')->where('group_id','in','2,3,4,5,6')->select();
            
            //根据用户表的信息 获取微信小程序的user_xcx 表中的openid unionid
            $ids = array_column($user,'id');

            $user_xcx = db('user_xcx')->where('uid','in',$ids)->select();
            //获取到里面的unionid
            $unionid_all =  array_column($user_xcx,'unionid');
            
            //拿unionid 获取到所有 要 发送 信息的 公众号的 openid
            $openid_all = db('user_gzh')->where('unionid','in',$unionid_all)->select();

            foreach($openid_all as $openid){
                # 公众号消息推送
                $res = $this->pushMessage([
                'openid' => $openid['openid'], // 用户openid
                'access_token' => $this->token,
                'template_id' => "K_-u4wpS9Sb5lJMvW1KUh6tF3TBNr8Qof6QV-A3YZc8", // 填写你自己的消息模板ID
                'data' => [ // 模板消息内容，根据模板详情进行设置
                    'first'    => ['value' => urlencode("有一笔新的订单,请尽快处理"),'color' => "#743A3A"],
                    'keyword1' => ['value' => urlencode($desknum),'color'=>'blue'],
                    'keyword2' => ['value' => urlencode($goods_name),'color'=>'blue'],
                    'keyword3' => ['value' => urlencode($user_name),'color' => 'green'],
                    'remark'   => ['value' => urlencode("快接单吧！"),'color' => '#743A3A']
                ],
                'url' => "http://mp.weixin.qq.com", // 消息跳转链接
            ]);
            }
            

            halt($res);

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
}
