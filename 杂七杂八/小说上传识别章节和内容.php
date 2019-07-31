<?php
class upload_txt{
    public function do_add_book_txt()
    { 
        //设置php.ini的值
        ini_set('memory_limit', '256M');
        //ini_set('memory_limit', '-1');
        //无时间上的限制
        set_time_limit(0);
        if (empty($_POST['tid']) || empty($_POST['title'])) {
            $this->error('内容提交不能为空');
        }
        $upload = new \Think\Upload();
        $upload->maxSize = 18388608;
        $upload->exts = array('jpg', 'png', 'jpeg', 'txt');
        $upload->rootPath = "./Public/Uploads/book/";
        $upload->savePath = '';
        $upload->autoSub = false;
        $upload->saveName = array('uniqid', '');
        //上传后,返回上传的结果,分别是图片和文件的数据
        $info = $upload->upload();
     
        if (!$info) {
            $this->error($upload->getError());
        }
        $t = M('type');
        $type = $t->where(array('id' => $_POST['tid']))->find();
        $w['name'] = $type['name'];
        $w['tid'] = $_POST['tid'];
        $w['title'] = $_POST['title'];
        $w['author'] = $_POST['author'];
        //获取到上传图片的名
        $w['img'] = $info['img']['savename'];
        $w['content'] = $_POST['content'];
        $w['sex'] = $_POST['sex'];
        $w['is_free'] = $_POST['is_free'];
        $b = M('book');
        //基本信息入库,返回一个主键id
        $i = $b->add($w);
        // var_dump($i).PHP_EOL; 
        if ($i > 0) {
            $bi = M('bookinfo');
            // if ($_POST['is_free'] == '1') {
            //     $_POST['gold'] = 0;
            // }
            $w2['tid'] = $_POST['tid'];
            $w2['bid'] = $i;
            $w2['is_free'] = $_POST['is_free'];
           // $w2['gold'] = $_POST['gold'];
            $w2['time'] = time();
            //获取到上传文件的信息
            $txt = $info['txt']['savename'];
            
            //获取上传的文件的内容
            $content = file_get_contents(UPLOAD.$txt);
                   
            //去空格
            $content = trim($content, "\xEF\xBB\xBF");

            //自动获取字符串编码
            $encode = mb_detect_encoding($content, array('UTF-8',"GBK","GB2312","ASCII",'BIG5')); 
            if($encode != 'UTF-8'){
                $content = iconv($encode, "UTF-8//IGNORE",$content);
            }
            //获取到文字的长度
            $w3['total_font'] = strlen($content);
            $w4['id'] = $i;
            $b->where($w4)->save($w3);

            $pattern = '/第\s*[一|二|三|四|五|六|七|八|九|十|百|千|零|\d]+\s*(章|节)+\s*+(.*?)\s+?/';
            //正则匹配
            preg_match_all($pattern, $content,$match,PREG_OFFSET_CAPTURE);

            //获取章节
            $titles = array();
            foreach ($match[0] as $key => $value) {
                $titles[$key] = $value[0];
            }

            //删除换行后的
            foreach($titles as &$title){
                trim($title);
                $num = mb_strpos($title,"\r",0,'UTF-8');
                $title = mb_substr($title,0,$num,'UTF-8');

                //现在标题长度
                if(mb_strlen($title,'UTF8')>30){
                    $title = mb_substr($title,0,30,'utf-8');
                }
            }

            
            //章节位置
            //将每一个章节的内容转换为数组
            $contents = array();
            foreach ($match[0] as $key => $value) {
                if($key == 0){
                    continue;
                }
                $start = $match[0][$key-1][1];
                $len = $value[1] - $match[0][$key-1][1] ;
                $temp_content = substr($content,$start,$len);
                $temp_content = str_replace($match[0][$key-1][0], "", $temp_content);
                $temp_content = str_replace("\r\n", "<br />", $temp_content);
                $contents[] = $temp_content;

                //最后的一部分
                if( (count($match[0]) - 1) == $key){
                    $last_content = substr($content,$value[1]);
                    $last_content = str_replace($value[0], "", $last_content);
                    $last_content = str_replace("\r\n", "<br />", $last_content);
                    $contents[] = $last_content;
                }
            }

            foreach ($contents as $ka => $va) {
                $w2['title2'] = $titles[$ka];
                $w2['content'] = $va;
                $w2['number'] = $ka + 1;
                //入库
                $res = $bi->add($w2);

            }
            $add_num = count($contents);
            $b = M('book');
            $b->where(array('id' => $i))->setInc('num', $add_num);
            unlink('Public/Uploads/book/' . $txt);
            $this->success('添加书籍成功');
        } else {
            $this->error('添加书籍失败，请重试');
        }
    }
}
?>