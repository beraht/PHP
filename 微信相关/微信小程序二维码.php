<?php

    /**
     * 二维码生成与保存
     * @param 与二维码相关联的参数
     */
    function add(){
        if ($this->request->isPost()) {
            $id = $this->request->post("desk_id/d");

       // $targeturl =  'https://mall.flan1688.com/admin/api/shop/shopqrcode/id/'.$id;
        /*$tmpurl = config('web_host').'/qrcode/build?text='.urlencode($targeturl).'&label='.$params['label'].'&logo=0&labelhalign=0&labelvalign=3&foreground=%23ffffff&background=%23000000&size=200&padding=10&logosize=50&labelfontsize=30&errorcorrection=medium';*/

        $appId = config("wx.appid");
        $appSecret = config("wx.secret");
        $wechat = new Wechat($appId, $appSecret);
        $token = $wechat->getAccessToken();//获取token
        $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=$token";
        $data = ['path' => '/api/goods/detail?id=' . $id, 'width' => 280];

        $resp = \HttpUtil::send($url, 'POST', json_encode($data));
        $res = json_decode($resp['data'], true);
        if (!empty($res['errcode'])) {
            //请求小程序码异常，刷新token
            $this->error(__('微信token失效，获取小程序码失败！'));

        }
        //$img = file_get_contents($tmpurl);
        $tmpdir = 'shopqrcode/'.$id%100;
        is_dir($tmpdir) OR mkdir($tmpdir, 0777, true);
        $imgurl = $tmpdir.'/'.$id.'.png';
        //file_put_contents($imgurl,$img);
        file_put_contents($imgurl, $resp['data']);

       // db('shop_qrcode')->where('id','=',$id)->update(['url'=>$targeturl,'img_url'=>'/'.$imgurl]);
       //db('rq_code')->where('id','=',$id)->update(['img'=>'/'.$imgurl]);
       $list = ['desk_id'=>$id,'img'=>'/'.$imgurl]; 
       db('rq_code')->saveAll($list);
    }