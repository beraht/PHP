<?php


// 对二维数组进行指定key排序 $arr 二维数组 ，$shortKey 需要排序的列，$short 排序方式 $shortType 排序类型

function multi_array_sort($arr,$shortKey,$short=SORT_DESC,$shortType=SORT_REGULAR)

{

    foreach ($arr as $key => $data){

        $name[$key] = $data[$shortKey];

    }

        array_multisort($name,$shortType,$short,$arr);

    return $arr;

}






  ?>