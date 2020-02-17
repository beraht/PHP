<?php
namespace app\admin\controller;
use app\common\Util;

class Images{

    public function index(){

        $file = request()->file('file');
        $info = $file->move('../public/static/upload');
        if($info){
            $data = [
                'image' => $info->getSaveName(),
            ];

            return Util::show(1,'ok',$data);
        
        }else{

            return Util::show(0,'error');
        }
    }


}