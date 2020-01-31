<?php

namespace app\api\model;

use think\Model;
use think\Db;

/**
 * 规格
 * create by WintonLi
 */
class GoodSpec Extends Model
{
    /**
     * 获取所有的规格数据
     * @param array $where 条件
     * @param string $field 选取字段
     * @param int $page 当前页数
     * @param int $pageSize 每页显示记录数
     * @param int $useCount 是否返回总行数 1为返回 0为不返回
     * @param int $getImage 是否返回图片列表 1为返回 0为不返回
     * @param string $order 排序方式
     * @return array
     */
    public function getList($where = array(), $field='*', $page=1, $pageSize=10, $useCount=0, $getImage=0, $order='sort desc')
    {
        $result = array('list'=>array());
       // $offset = ($page-1) * $pageSize;

        // if($useCount){
        //     $result['total'] = $this->where($where)->count();
        // }

        $result = db('GoodSpec')
            ->field($field)
            ->where($where)
            ->order($order)
            ->select();

        if(empty($result)){
            return $result;
        }
        $result =  dataGroup($result,'type');    
        return $result;
    }
    /**
     * 获取所有的规格数据
     * @param array $where 条件
     * @param string $field 选取字段
     * @param int $page 当前页数
     * @param int $pageSize 每页显示记录数
     * @param int $useCount 是否返回总行数 1为返回 0为不返回
     * @param int $getImage 是否返回图片列表 1为返回 0为不返回
     * @param string $order 排序方式
     * @return array
     */
    public function getAllList($where = array(), $field='*', $page=1, $pageSize=10, $useCount=0, $getImage=0, $order='sort desc')
    {
        $result = array('list'=>array());
        $result = db('GoodSpec')
            ->field($field)
            ->where($where)
            ->order($order)
            ->select();

        if(empty($result)){
            return $result;
        }   
        return $result;
    }
   
}