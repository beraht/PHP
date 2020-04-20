<?php
namespace app\controller;

use app\BaseController;

class Demo extends BaseController
{
    public function show()
    {
        return 'json';
    }

    //调用一个对象中不存在或被权限控制中的方法，__call 方法将会被自动调用。
    /**
     * @param string $name  访问的方法
     * @param  string $arguments   携带的参数
     */
    public function __call($name, $arguments)
    {
        $result = [
            'status' => 0,
            'msg'  => "找不到该方法",
            'result' => null,
        ];
        return  json($result,400);
    }


}
