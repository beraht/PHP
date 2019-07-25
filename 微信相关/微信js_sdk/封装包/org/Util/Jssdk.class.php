<?php
namespace Org\Util;

/**
 * 微信JSSDK类
 */
class JSSDK {
  private $appId;
  private $appSecret;

  public function __construct($appId, $appSecret) {
      $this->appId = $appId;
      $this->appSecret = $appSecret;
  }

  public function getSignPackage() {
    //获取jsapi_ticket
    $jsapiTicket = $this->getJsApiTicket();

    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    //当前时间戳
    $timestamp = time();
    //获取随机字符串
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("./weixinjs/jsapi_ticket.json"));

    //判断是否过期 jsapi_ticket的有效期7200秒
    if ($data->expire_time < time()) {

      //请求接口携带 appId appSecret  , 获取到access_token
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      //curl请求后返回参数
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      //存入本地文件,用于判断时间7200是否过期
      if ($ticket) {
        $data->expire_time = time() + 7000;
        $data->jsapi_ticket = $ticket;
        file_put_contents("./weixinjs/jsapi_ticket.json",json_encode($data));
      }
    } else {
      $ticket = $data->jsapi_ticket;
    }

    return $ticket;
  }

  private function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("./weixinjs/access_token.json"));
    if ($data->expire_time < time()) {
      // 如果是企业号用以下URL获取access_token
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $data->expire_time = time() + 7000;
        $data->access_token = $access_token;
        file_put_contents("./weixinjs/access_token.json",json_encode($data));
      }
    } else {
      $access_token = $data->access_token;
    }
    return $access_token;
  }

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }

  /**
   * 获取当前页面完整URL地址
   * @return str
   */
  public static function getShareUrl($share_member = 0) {
      $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
      $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
      $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
      $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);

      // 将会员ID拼接进去
      $share_member = self::encrypt((int)$share_member);
      $relate_url = (strpos($relate_url, '&') === false) ? $relate_url . '&share_member=' . $share_member : '?share_member=' . $share_member;
      return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
  }

  /**
   * 获取当前域名
   * @return str
   */
  public static function getDomain() {
      $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
      return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
  }

  /**
   * 加密函数
   *
   * @param string $txt 需要加密的字符串
   * @param string $key 密钥
   * @return string 返回加密结果
   */
  public static function encrypt($txt, $key = 'jssdk'){
      if (empty($txt)) return $txt;
      if (empty($key)) $key = md5(MD5_KEY);
      $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
      $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
      $nh1 = rand(0,64);
      $nh2 = rand(0,64);
      $nh3 = rand(0,64);
      $ch1 = $chars{$nh1};
      $ch2 = $chars{$nh2};
      $ch3 = $chars{$nh3};
      $nhnum = $nh1 + $nh2 + $nh3;
      $knum = 0;$i = 0;
      while(isset($key{$i})) $knum +=ord($key{$i++});
      $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
      $txt = base64_encode(time().'_'.$txt);
      $txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
      $tmp = '';
      $j=0;$k = 0;
      $tlen = strlen($txt);
      $klen = strlen($mdKey);
      for ($i=0; $i<$tlen; $i++) {
          $k = $k == $klen ? 0 : $k;
          $j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
          $tmp .= $chars{$j};
      }
      $tmplen = strlen($tmp);
      $tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
      $tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
      $tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
      return $tmp;
  }

  /**
   * 解密函数
   *
   * @param string $txt 需要解密的字符串
   * @param string $key 密匙
   * @return string 字符串类型的返回结果
   */
  public static function decrypt($txt, $key = 'jssdk', $ttl = 0){
      if (empty($txt)) return $txt;
      if (empty($key)) $key = md5(MD5_KEY);

      $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
      $ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
      $knum = 0;$i = 0;
      $tlen = @strlen($txt);
      while(isset($key{$i})) $knum +=ord($key{$i++});
      $ch1 = @$txt{$knum % $tlen};
      $nh1 = strpos($chars,$ch1);
      $txt = @substr_replace($txt,'',$knum % $tlen--,1);
      $ch2 = @$txt{$nh1 % $tlen};
      $nh2 = @strpos($chars,$ch2);
      $txt = @substr_replace($txt,'',$nh1 % $tlen--,1);
      $ch3 = @$txt{$nh2 % $tlen};
      $nh3 = @strpos($chars,$ch3);
      $txt = @substr_replace($txt,'',$nh2 % $tlen--,1);
      $nhnum = $nh1 + $nh2 + $nh3;
      $mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
      $tmp = '';
      $j=0; $k = 0;
      $tlen = @strlen($txt);
      $klen = @strlen($mdKey);
      for ($i=0; $i<$tlen; $i++) {
          $k = $k == $klen ? 0 : $k;
          $j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
          while ($j<0) $j+=64;
          $tmp .= $chars{$j};
      }
      $tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
      $tmp = trim(base64_decode($tmp));

      if (preg_match("/\d{10}_/s",substr($tmp,0,11))){
          if ($ttl > 0 && (time() - substr($tmp,0,11) > $ttl)){
              $tmp = null;
          }else{
              $tmp = substr($tmp,11);
          }
      }
      return $tmp;
  }

}

