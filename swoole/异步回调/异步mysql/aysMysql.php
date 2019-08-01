<?php

class asyMysql{
    /**
     * mysql 配置
     */
    public $dbConfig = [];
    /**
     * 异步mysql实例
     */
    public $dbSource = '';

    public function __construct()
    {
        //new swoole_mysql;
        $this->dbSource = new Swoole\Mysql;
        $this->dbConfig = [
            'host' => '192.168.56.102',
            'port' => 3306,
            'user' => 'test',
            'password' => 'test',
            'database' => 'test',
            'charset' => 'utf8', //指定字符集
            'timeout' => 2,  // 可选：连接超时时间（非查询超时时间），默认为SW_MYSQL_CONNECT_TIMEOUT（1.0）
        ];
    }

    /**
     * mysql 执行的逻辑
     * 1.先连接数据库,需要二个参数,一个配置信息,一个回调函数
     * 2.编写sql语言,curd;
     */
    public function execute($id,$username){

        //回调函数里面,有二个参数,第一个是当前连接mysql的实例,一个是否连接成功的bool值
        $this->dbSource->connect($this->dbConfig,function($db,$result)use($username,$id){//PHP作用域,函内无法获取函数外,用闭包引入
            if($result==false){
                //输出连接失败的信息
                echo $db->connect_error();
                return;
            }
            //要执行的sql语句
            //$sql = 'select * form test where id = 1';
            $sql = "update test set `username` = '".$username."' where id=".$id;
            //执行sql语句(curd)
            $db->query($sql,function($db,$result){
                //select => 返回的是结果集
                //add update delect 返回的是bool
                if($result == false){

                    echo "sql语句执行失败或者语句有误";

                }elseif($result == true){
                    
                    echo "插入或者更新删除字段成功";

                }else{
                    echo "select 返回的结果集";
                }
                return true;
                //执行完毕后,关闭mysql连接
                $db->close();

            });


        });    
    }
    
    /**
     * MySQL 数据更新
     *
     * @return void
     */
    public function update(){

    }
    /**
     * MySQL 数据插入
     *
     * @return void
     */
    public function add(){

    }

}

$obj = new asyMysql();
//需要将id=1的name改为xiaojie
$flag = $obj->execute(1,'xiaojie');
var_dump($flag)

?>