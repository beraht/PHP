<?php
$a = "函数外的变量";

function get(){
    //按照PHP的作用域,局部无法访问全局的
    echo  $a;

}

get();


//可以和上级作用域产生了联系了,实现了闭包
//连接闭包和外界变量的关键字：USE
$clo = function () use ($a){
    echo $a; //输出
};

$clo();




?>