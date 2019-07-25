<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
</body>
<script>
/**
   * 自定义方法，校验form数据
   */
  submitForm: function (e) {　　　　//这里是小程序wxml提交form
    var that = this;
//#code ，注意这里的form数据你要校验哦。
wx.request({
        //点击支付后请求的地址
        url: 'https://m.******.com/Home/Xiaoxxf/make_order',
        header: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        //请求的方式
        method: "POST",
        //发送的数据
        data: { openid: wx.getStorageSync('openid'), data_name: e.detail.value.data_name, data_phone: e.detail.value.data_phone, data_IDcard: e.detail.value.data_IDcard, data_num: e.detail.value.data_num, data_addr: e.detail.value.data_addr, data_remark: e.detail.value.data_remark, data_total: e.detail.value.data_num * that.data.unitPrice,a_id:that.data.a_id},

        success: function (res) {
          if (res.data.state==1) {
     // --------- 订单生成成功，发起支付请求 ------------------
            wx.requestPayment({ 
              timeStamp: res.data.timeStamp,
              nonceStr: res.data.nonceStr,   //字符串随机数
              package: res.data.package,
              signType: res.data.signType,
              paySign: res.data.paySign,
              'success': function (res) {
                console.log(res.errMsg);    //requestPayment:ok==>调用支付成功
                  wx.showToast({
                    title: '支付成功',//这里打印出报名成功
                    icon: 'success',
                    duration: 1000
                  })
               },
              'fail': function (res) { 
                console.log(res.errMsg);
              },
              'complete': function (res) {
                console.log(res.errMsg);
               }
            })
          } else if (res.data.state == 0){
            wx.showToast({
              title: res.data.Msg,
              icon: 'fail',
              duration: 1000
            })
          }else{
            wx.showToast({
              title: '系统繁忙，请稍后重试~',
              icon: 'fail',
              duration: 1000
            })
          }
        }

      })
}
</script>
</html>