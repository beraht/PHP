<?php
/**
 * 多文件文件上传封住
 * @param  [type]  array $fileInfo $_FILES["myFile"] 
 * @return [type]  string         [文件保存的路径]
 */
function uploadFile($fileInfo){
    header('content-type:text/html;charset=utf-8');
    //接收文件,临时文件信息
    //$fileInfo = $_FILES["myFile"];//降准操作
    $fileName = $fileInfo['name'];
    $tmp_name = $fileInfo['tmp_name'];
    $size = $fileInfo['size'];
    $error = $fileInfo['error'];    
    $type = $fileInfo['type'];

    //服务器端设置限定
    $maxsize = 10485760;//10M,10*1024-1024
    $allowExt = array('jpeg','jpg','png','tif'); //允许上传的文件类型(扩展名)
    $ext = pathinfo($fileName,PATHINFO_EXTENSION);//提取上传文件的扩展名

    //目标存放文件夹
    $path = 'uploads';
    if(!file_exists($path)){ //当目录不存在,就创建目录
        mkdir($path,0777,true); //创建目录
        chmod($path,0777); //改变文件模式,所有人都有rwx权限
    }
    //得到唯一的文件名,防止文件名重复产生覆盖
    $uniName = md5(uniqid(microtime(true),true)).".$ext.";//md5加密,uniqid产生唯一id,microtime做前缀
    //目标存放文件地址
    $destination = $path."/".$uniName;
    //当文件上传成功,存入临时文件夹,服务器端开始判断
    if($error ===0){
        if($size>$maxsize){
            exit("上传文件过大");
        }
        if(!in_array($type,$allowExt)){
            exit("非法文件类型");
        }
        if(!is_uploaded_file($tmp_name)){
            exit("上传方式有误,请使用post方法");
        }
        //判断是否为真实图片(防止伪装成图片的病毒)
        if(!getimagesize($tmp_name)){
            exit("不是真的的图片类型");        
        }
        //move_upload_file($tmp_name , "uploads/.$filename");
        if(@move_uploaded_file($tmp_name,$destination)){//@错误抑制符
            echo "文件".$fileName."上传成功";
        }else{

            echo "文件".$fileName."上传失败"; 
        }

    }else{
        switch ($error) {
            case 1:
                echo "超过上传文件的最大限制,请上传2M一下文件";
                break;
            case 2:
                echo "上传文件过多,请一次上传20个以下文件";
                break;
            case 3:
                echo "文件并未完全上传,请再次尝试";
                break;
            case 4:
                echo "超过上传文件的最大限制,请上传2M一下文件";
                break;         
            case 7:
                echo "没有临时文件";
                break; 
        }
    }
    return $destination; 
}



/**
 * 调用方法
 * 1.接收多图片
 * 2.调用方法
 */
foreach ($_FILES as $fileInfo) {
    $file[]= uploadFile($fileInfo);
}




?>