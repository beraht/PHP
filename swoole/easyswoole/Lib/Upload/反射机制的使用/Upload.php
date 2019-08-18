<?php
namespace App\HttpController\Api;

use App\HttpController\Api\Base;
use EasySwoole\Component\Di;
// use App\Lib\Upload\Video;
// use App\Lib\Upload\Image;
//反射机制的类
use App\Lib\ClassArr;
class Upload extends Base
{

    public function file(){
        //接收图片  
        $request = $this->request();
        $files = $request->getSwooleRequest()->files;
        $types = array_keys($files);
        $type = $types[0];
        if(empty($type)){
            return $this->writeJson(400,[],'上传失败');
        } 
       

        /**
         * 反射机制的使用
         * 1.先实例化 反射类
         * 2.获取到类,方法里面定义的 
         * 3.建立反射类,实例化对应的类
         */



        try{

        //    $obj = new Video($request);
        //     //$obj = new Image($request);
          //php的反射机制的使用  
           $classObj  = new ClassArr();
           //返回的是管理的类的数组
           $uploadClassStat = $classObj->uploadClassStat();
           //建立反射类,实例化对应的类 参数一:类型(key) 二:管理的类的数组 三: 获取的上传的信息
           $uploadObj = $classObj->initClass($type,$uploadClassStat, [$request,$type]);

           $file = $uploadObj->upload();

        }catch(\Exception $e){
            return $this->writeJson(400,[],$e->getMessage());
        }

        if(empty($file)){

            return $this->writeJson(400,[],'上传失败');

        }

        $data = [
            "url" => $file,
        ];

        return $this->writeJson(400,$data,'上传成功');


    }

}
