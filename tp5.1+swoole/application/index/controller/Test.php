<?php
namespace app\index\controller;
use app\common\aliyun\Sms;
use app\common\Util;
use app\common\redis\Predis;
use app\common\Common;
class Test extends Common
{
    public function index()
    {

        return  "index/index/index" .'-'. time();
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }

}
