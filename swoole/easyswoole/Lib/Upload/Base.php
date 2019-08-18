<?php

namespace App\Lib\Upload;
use App\Lib\Utils;

class Base {

    /**
     * 上传文件的 file - key值
     */
    public $type = "";

    public function __construct($request,$type=null)
    {
       $this->request = $request;
       //获取文件的信息,并获取key
       if(empty($type)){
            $files = $this->request->getSwooleRequest()->files;
            $types = array_keys($files);
            $this->type = $types[0];
        }else{
            $this->type = $type;
        }

    }

    public function upload(){
        if($this->type != $this->fileType){
            return false;
        }
        //获取文件上传的信息
        $videos = $this->request->getUploadedFile($this->type);

        $this->size = $videos->getSize();
        //判断文件大小是否符合( 多个判断,如图片和视频的判断大小)
        $this->CheckSize();
        //获取上传的完整的文件名(文件+后缀)
        $fileName = $videos->getClientFileName();
        //获取文件上传的类型
        $this->mediaType= $videos->getClientMediaType();
        //检查文件的格式
        $this->checkmediaType();
        //返回文件名路径完整,
        $file = $this->getFile($fileName);
        print_r($file).PHP_EOL;
        print_r($this->file);
        //移动文件
        $flag = $videos->moveTo($file);
        if(!empty($flag)){
            return $this->file;
        }

        return false;


    }
    /**
     * 文件写入到服务器 , 文件的路劲,唯一性的文件名,加上文件的后缀
     *
     * @param [type] $fileName
     * @return void
     */
    public function getFile($fileName){

        $pathinfo = pathinfo($fileName);
        $extension = $pathinfo['extension'];
        $dirname =  "/" . $this->type . "/" .  date("Y") . "/" .date("m");
        $dir = EASYSWOOLE_ROOT . "/webroot" . $dirname;
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }

        $basename =  "/" . Utils::getFileKey($fileName).'.'.$extension;
        $this->file = $dirname . $basename;
        return $dir . $basename;
    }

    //检查文件的格式是否正确
    public function checkmediaType(){
       $mediaType = explode('/',$this->mediaType);
       $mediaExtType = $mediaType[1] ?? "";

       if(empty($mediaType)){
            throw new \Exception("上传{$this->type}文件不合法");
       }
       if(!in_array($mediaExtType,$this->fileExtTypes)){
            throw new \Exception("上传{$this->type}文件格式不合法");
       }
       return true;
    }


    //检查文件大小是否符合要求
    public function CheckSize(){
        if(empty($this->size)){
           return false;

        }

        if(!empty($this->fileType) && $this->fileType='image'){
            if($this->imgSize  <  $this->size){
                return false;
            }
      

        }elseif(!empty($this->fileType) && $this->fileType='video'){
            if($this->videoSize  <  $this->size){
                return false;
            }
     
        }    

    }


}