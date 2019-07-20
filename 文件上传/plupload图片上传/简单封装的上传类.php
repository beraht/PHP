//图片上传
    public function uploadimg(){

        $file = $_FILES['file'];
        
        $suffix =  strtolower(strrchr($file['name'],'.'));
        $type = ['.jpg','.jpeg','.gif','.png'];
        if(!in_array($suffix,$type)){
            print_r(['status'=>'img type error']);
            return;
        }

        if($file['size']/1024>5120){
            print_r(['status'=>'img is too large']);
            return;
        }

        $filename =  uniqid("cart_img_",false);
        $date = date('Ymd',time());
        $uploadpath = MARTROOT.$date."/";
        if(!file_exists($uploadpath)){
            mkdir($uploadpath,0777,true);
        };
        $file_up = $uploadpath.$filename.$suffix;
        $re = move_uploaded_file($file['tmp_name'],$file_up);
        if($re){
            $name = $date."/".$filename.$suffix;
            print_r(show(1,'图片上传成功',['img_name'=>$name]));
            return;
        }else{
            print_r(show(0,'图片上传失败',[]));
            return;
        }
    }