<?php
namespace app\common;

class Util{

    /**
     * api 输出格式
     * @param int  $status 状态值
     * @param string $message  提示
     * @param arrary  $data 数据
     * @return json 
     */
    public static function show($status,$message='',$data=[]){
        $result = [
            'static' => $status,
            'message' => $message,
            'data' => $data,
        ];
        
        return json_encode($result);
    }
    
}
