<?php

namespace App\Lib\Upload;

class Base {

    /**
     * 上传文件的 file - key值
     */
    public $type = "";

    public function __construct($request )
    {
       $this->request = $request;
       //获取文件的信息,并获取key
       $files = $this->request->getSwooleRequest()->files;
       $types = array_keys($files);
       $this->type = $types[0];

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
        //获取上传的文件名
        $fileName = $videos->getClientFilename();
        //获取文件上传的类型
        $this->mediaType= $videos->getClientMediaType();
        //检查文件的格式
        $this->checkmediaType();




    }

    //检查文件的格式是否正确
    public function checkmediaType(){
        print_r($this->mediaType);


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