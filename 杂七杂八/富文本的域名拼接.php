<?php

    function getDetailsAttr($value, $data = [])
    {   
        $value = $value ? $value : (isset($data['content']) ? $data['content'] : '');
        /*替换文章中图片/视频链接 begin*/
        $preg = '/<[img|IMG|video|VIDEO].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png|\.mp4|\.avi]))[\'|\"].*?[\/]?>/';
        preg_match_all($preg, $value, $images);
        if(isset($images[1]) && !empty($images[1])) {
            $web_host = config('web_host');
            foreach ($images[1] as $key => $images_item) {
                if(substr( $images_item, 0, 1 ) == '/'){
                    $path = $web_host . $images_item;
                    $value = str_replace($images_item, $path, $value);
                }
            }
        }
        /*替换文章中图片/视频链接 end*/
        return $value;
    }


    //使用方法(富文本内容传入)
    getDetailsAttr($good['content']);