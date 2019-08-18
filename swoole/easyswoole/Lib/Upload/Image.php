<?php
namespace App\Lib\Upload;
use App\Lib\Upload\Base;

class Image extends Base{

    /**
     * fileType
     */
    public $fileType = "image";
    /**
     * 限制文件大小
     */
    public $imgSize = 5;
    /**
     * 限制文件的格式
     */
    public $fileExtTypes = [
        'png',
        'jpeg',
    ];


}