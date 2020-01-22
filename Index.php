<?php

namespace app\api\controller;

use app\common\controller\Api;
use Doctrine\Common\Cache\Cache as DoctrineCache;
use think\cache;

/**
 * 首页接口
 */
class Index extends Api
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 获取推荐商品(获取指定二级分类下的数据)
     * @param int page 
     * @param int pageSize
     * @param  int  category_id_two  可选(用户获取指定的分类下的数据)
     * @param string  type  price_up  price_down  pay_up  pay_down
     */
    public function recom_good(){
        $page = $this->request->param('page');
        $pageSize = $this->request->param('pageSize');
        $category_id_two = $this->request->param('category_id_two');
        $type = $this->request->param('type');

        if(!empty($category_id_two)){
            $where['category_id_two'] = ['=',$category_id_two];
        }

        $order = 'sort desc';

        if($type == 'price_up'){
            $order = 'price asc';
        }elseif($type == 'price_down'){
            $order = 'price desc';
        }elseif($type == 'pay_up'){
            $order = 'sales_sum asc';
        }elseif($type == 'pay_down'){
            $order = 'sales_sum desc';
        }

        if(!$page || !$pageSize){
            $this->error("传递的参数有误");
        }
        $where['status'] = ['=',0];
        $where['is_adopt'] = ['=',1];
        $count = db('good')->where($where)->count();
        $pages = ($page-1)*$pageSize;
        $total_page = ceil($count / $pageSize);
        if($total_page < $page){
            $ret['sta'] = 1;
            $ret['list'] = [];
            $this->success('成功',$ret);
        }

        $good = db('good')->where($where)->field('*')->order($order)->limit($pages,$pageSize)->select();
        foreach($good as $k=>$g){
            $good[$k]['photo'] = imgUrl($g['photo']);
        }
        $ret['sta'] = 1;
        $ret['list'] = $good;
        $this->success('获取成功',$ret);
        

    }

    /**
     * 获取所有的一级分类
     */
    public function get_one_category(){
        //获取首页的一级
        $cates  =  db('good_items')->where('status','=',1)->limit(9)->order('sort desc')->select();
        foreach($cates as &$cate){
            $cate['photo'] = imgUrl($cate['photo']);
        }

        $ret['sta'] = 1;
        $ret['list'] = $cates;
        $this->success('获取成功',$ret);
    }



    /**
     * 获取所有的类型及第一个所有的二级分类
     * @return json
     * @param items_id 一级分类id (指定类型)
     */
    public function category(){
        $items_id = $this->request->param('items_id/d');
        //获取类型
        $types = db('good_items')->where('status','eq',1)->order('sort desc')->select();
        if(empty($types)){
            $ret['sta'] = 0;
            $ret['list'] = [];
            $this->success('获取类型失败',$ret);
            return;
        }

        //没有指定类型,默认获取第一个
        if(empty($items_id)){
            //获取第zhid个类型下的所有二级分类
            $type_id = $types[0]['id'];
            $where = ['class_id'=>['=',$type_id],'status'=>['=',1]];
            $category =  db('good_category')->where($where)->order('sort desc')->select();
        }else{
            $where = ['class_id'=>['=',$items_id],'status'=>['=',1]];
            $category =  db('good_category')->where($where)->order('sort desc')->select();
        }
            
        if(empty($category)){
            $ret['lst'] = $types;
            $ret['sta'] = 1;
            $this->success('获取类型成功',$ret);
        }


        foreach($category as &$cate){
            $cate['photo'] = imgUrl($cate['photo']);
        }

        $ret['lst'] = $types;
        $ret['sta'] = 1;
        $ret['list'] = $category;
        $this->success('获取成功',$ret);
    }

    /**获取某个类型下的所有二级分类
     * @param class_id 产品ID
     * @return json 
     */
    public function category_deatil(){
        $type_id = $this->request->param('class_id/d');
        if(empty($type_id)){
            $this->error('传递参数有误');
        }
        $where = ['class_id'=>['=',$type_id],'status'=>['=',1]];
        $category =  db('good_category')->where($where)->order('sort asc')->select();
        if(empty($category)){
            $ret['sta'] = 1;
            $ret['list'] = [];
            $this->success('获取成功',$ret);
            return;
        }

        foreach($category as &$gs){
            $gs['photo'] = imgUrl($gs['photo']);
        }

        if(empty($category)){
            $ret['sta'] = 0;
            $ret['list'] = [];
            $this->success('获取失败',$ret);
            return;
        }


        $ret['sta'] = 1;
        $ret['list'] = $category;
        $this->success('获取成功',$ret);
    }

    // /**
    //  * 获取二级分类下的所有数据
    //  */
    // public function get_goos


    /**
     * 竞价商品首页(一个时间段,只有一场活动)
     */
    public function  bidding_price(){

        try{
            //获取到竞拍中的商品(结束时间大于当前   竞拍时间小于 当前时间(未开始)  )
            $where['end_time'] = ['EGT',time()];
            $where['auc_time'] = ['ELT',time()];
            $result['list'] = model('AuctionAct')->getList($where);

            //获取到还没有开始竞拍的商品(竞拍时间大于当前时间(未开始),)
            $where['auc_time'] = ['EGT',time()];
            $result['lst'] = model('AuctionAct')->getList($where);
        }catch(\Exception $e){
            
            $ret['sta'] = 0;
            $ret['list'] = [];
            $this->success('获取失败', $ret);

        }
        //进行中为空
        if(!empty($result['list']) && !empty($result['list']['goodlist'])){
            //获取商品的最新价格(获取到活动)
            $act_id = $result['list']['goodlist'][0]['act_id'];
            $auc_deatil = db('auction_detail')->where('act_id','=',$act_id)->select();
            foreach($result['list']['goodlist']  as $k=>$res){
                $result['list']['goodlist'][$k]['new_auc_price'] = $res['price'];
                foreach($auc_deatil as $auc){
                    if($res['act_id'] == $auc['act_id'] && $res['id'] == $auc['good_id']){
                        $result['list']['goodlist'][$k]['new_auc_price'] = $auc['new_auc_price'];
                    }
                }
            } 
        }
        
        $ret['sta'] = 1;
        $ret['list'] = $result;
        $this->success('获取成功', $ret);
    }

    /**
     * 进入竞拍商品详细
     * @param int  good_id 拍卖的id
     * @param  int act_id  活动id
     */
    public function get_good_auction(){
        //判断用户身份
        $userInfo = $this->auth->getUserinfo();//数组

        if(empty($userInfo))
            $this->error(__('未登录'));

        $user_id = $userInfo['id']; 

        $good_id = $this->request->param('good_id/d');
        $act_id = $this->request->param('act_id/d');

        if(empty($good_id) || empty($act_id)){
            $this->error(__('传递参数有误!'));
        }


        //判断当前用户是否缴纳保证金
        $w['good_id'] = ['=',$good_id];
        $w['act_id'] = ['=',$act_id];
        $w['userId'] = ['=',$user_id];
        $w['status'] = ['=',1];
        $res = db('auction_order')->where($w)->find();

        if(!empty($res)){
            $ret['bond_money_status'] = $res['status'];
        }else{
            $ret['bond_money_status'] = 0;
        }
        
        //获取商品
        $info = db('auction_good')->where('id','=',$good_id)->find();
        $info['photo'] = imgUrl($info['photo']);
        //获取图片
        $imgs = db('auction_images')->where('goodId','=',$good_id)->field('pic')->select();
        $img = [];
        foreach($imgs as $k=>$im){
           $img[] = imgUrl($im['pic']);
        }
        $info['videoUrl'] = imgUrl($info['videoUrl']);
        $info['imgs'] = $img;
        if(empty($info)){
            $ret['sta'] = 0;
            $ret['list'] = [];
            $this->success('获取失败', $ret);
        }

        //获取商品所属活动 以及当前活动下商品的最新价格和人数
        $art = db('auction_act')->where('id','=',$act_id)->find();
        $where['act_id'] = ['=',$act_id];
        $where['good_id'] = ['=',$good_id];
        $auc_detail = db('auction_detail')->where($where)->find();

        //获取当前用户的最近加价的金额
        $where['userId'] = ['=',$user_id];
        $auc_log = db('auction_log')->where($where)->order('id desc')->find();

        $ret['user_auc_detail'] = $auc_log;

        $ret['sta'] = 1;
        $ret['good_deatil'] = $info;
        $ret['act'] = $art;
        $ret['auction_detail'] = $auc_detail;
        $this->success('获取成功', $ret);


    }


    /**
     * 获取库存
     * @param int goodId 商品id
     * @param int good_spec_one  二级规格id
     * @param int good_spec_two  二级规格id
     */
    public function get_stock(){
        $good_id = $this->request->param('goodId/d');
        $good_spec_one = $this->request->param('good_spec_one/d');
        $good_spec_two = $this->request->param('good_spec_two/d');
        if(empty($good_id)|| empty($good_spec_one)|| empty($good_spec_two)){
            $this->error(__('传递参数有误!'));
        }
        //
        $where['goodId'] = $good_id;
        $where['good_spec_one'] = $good_spec_one;
        $where['good_spec_two'] = $good_spec_two;
        $data = db('good_stock')->where($where)->find();
        if(empty($data)){
            $data = ['stock'=>0];
            $ret['sta'] = 0;
            $ret['list'] = $data;
            $this->success('获取失败', $ret);
        }
        $ret['sta'] = 1;
        $ret['list'] = $data;
        $this->success('获取成功', $ret);

    }




    //获取产品规格库存
    /**
     * @param int good_id  商品id
     */
    public function get_good_spec(){
        $good_id = $this->request->param('good_id/d');
        if(empty($good_id)){
            $this->error(__('传递参数有误!'));
        }

        //获取这个商品的规格
        $good = db('good')->where('id','=',$good_id)->field('id,good_spec_one,good_spec_two,type_one,type_two')->find();

        $type_one = db('good_spec_type')->where('id','=',$good['type_one'])->find();

        $type_two = db('good_spec_type')->where('id','=',$good['type_two'])->find();

        $ids_one = explode(',',$good['good_spec_one']);
        $spec_one = db('good_spec')->where('id','in',$ids_one)->select();    
        $ids_two = explode(',',$good['good_spec_two']);
        $spec_two = db('good_spec')->where('id','in',$ids_two)->select(); 

       $ret['sta'] = 1;
       $ret['list']['one_name'] = $type_one['item_name'];
       $ret['list']['two_name'] = $type_two['item_name'];
       $ret['list']['one'] = $spec_one;
       $ret['list']['two'] = $spec_two; 
      
       $this->success('成功',$ret);

    }
    //测试,解除关系
     public function test(){
    //     $userObj = new \app\common\model\User();
    //     $userObj->remove_inviter(22610);
    // }  
    // //增加库存
    // $add = new \app\api\model\Good();
    // $res = $add->add_stock(2403,14007,14062,3);
    //     halt($res);

        // $auth = new \app\common\library\Auth;
        // $auth->direct(3);
        // $res = $auth->getToken();
        $res =  strtotime('+3 day', 1572007308);
        halt($res);
//        $phone = $this->request->param("phone");
//        $code = mt_rand(1000,9999);
// //Cache::set($phone,$code,3600);
//        halt( Cache::get($phone));
    }


    /**
     * 获取轮播图
     */
    public function get_banner()
    {
        // //判断用户身份
        // $userInfo = $this->auth->getUserinfo();//数组

        // if(empty($userInfo))
        //     $this->error(__('未登录'));

        // $userObj = new \app\api\model\User();
        // $group = $userObj->getGroup();

        //普通用户获取顶部banner
        $where = array();
        $where['status'] = ['=', 1];
        $where['startTime'] = ['<', time()];
        $where['endTime'] = ['>', time()];

        $field = '*';

        $bannerObj = new \app\api\model\Banner();
        $banner = $bannerObj->getList($where, $field);

        $this->success('请求成功', array(
            'bannerList' => empty($banner['list']) ? array() : $banner['list'],
        ));
    }

    /**
     * 首页栏目数据获取
     */
    public function column()
    {
        $type = $this->request->param('type');//首页加载页面类型：1今日秒抢，2品牌专区，3预告
        $page = $this->request->param('page');
        $page = ($page > 0) ? $page : 1;
        $pageSize = $this->request->param('pageSize');
        $pageSize = ($pageSize > 0) ? $pageSize : 10;

        //判断用户身份
        $userInfo = $this->auth->getUserinfo();//数组

        if(empty($userInfo))
            $this->error(__('未登录'));

        //获取品牌及旗下商品信息
        $goodObj = new \app\api\model\Good();
        $where = array();
        $where['status'] = ['=', 1];
        if ($type == 1)
            $where['columnId'] = ['in', [1,3]];
        else
            $where['columnId'] = ['in', [3,4]];

        $where['endTime'] = ['>', time()];

        if ($type == 3)
            $where['startTime'] = ['>', time()];
        else
            $where['startTime'] = ['<=', time()];

        $result = $goodObj->getBrandList($where, '*', $page, $pageSize);
        $brand = $result['list'];

        if (!empty($brand)) {
            foreach ($brand as $key => $val) {
                $brand[$key]['logo'] = empty($val['logo']) ? '' : $val['logo'] . config('brand_style');
                $brand[$key]['photo'] = empty($val['photo']) ? '' : $val['photo'] . config('good_cover_style');

                //获取对应品牌商品
//                $where = array();
//                $where['brandId'] = ['=', $val['id']];
//                $where['status'] = ['=', 0];
//
//                $good = $goodObj->getList($where, '*', 1, 6, 1, 1);
//
//                $brand[$key]['goodList'] = $good['list'];
//                $brand[$key]['amount'] = $good['total'];

                //判断用户是否关注预告
                if ($type == 3) {
                    $concern = db('user_concern')
                        ->where('associatedId', '=', $val['id'])
                        ->where('userId', '=', $userInfo['id'])
                        ->where('status', '=', 0)
                        ->find();

                    if (empty($concern))
                        $brand[$key]['isattention'] = 1;//未关注
                    else
                        $brand[$key]['isattention'] = 0;//已关注
                }
            }
        }

        $this->success('请求成功', array('brandList' => empty($brand) ? array() : $brand));
    }

    /**
     * 搜索历史
     */
    public function historySearch()
    {
        $userInfo = $this->auth->getUserinfo();

        if(empty($userInfo))
            $this->error(__('未登录'));

        $result = db('search_words')
            ->field('word')
            ->where('userId', '=', $userInfo['id'])
            ->where('is_hide', '=', 0)
            ->limit(0, 20)
            ->order('updateTime desc')
            ->select();

        $this->success('请求成功', array('searchList' => $result));
    }

    /**
     * 删除/清空搜索历史
     */
    public function deleteSearch()
    {
        $word = $this->request->param('word');
        $userInfo = $this->auth->getUserinfo();

        if(empty($userInfo))
            $this->error(__('未登录'));

        if (empty($word)) {
            //清空搜索历史
            db('search_words')
                ->where('userId', '=', $userInfo['id'])
                ->where('is_hide', '=', 0)
                ->update(['is_hide' => 1]);
        } else {
            //删除单个搜索历史
            db('search_words')
                ->where('userId', '=', $userInfo['id'])
                ->where('is_hide', '=', 0)
                ->where('word', '=', $word)
                ->update(['is_hide' => 1]);
        }

        $this->success('删除成功', array());
    }

    /**
     * 搜索
     */
    public function search()
    {
        $page = $this->request->param('page');
        $page = ($page > 0) ? $page : 1;
        $pageSize = $this->request->param('pageSize');
        $pageSize = ($pageSize > 0) ? $pageSize : 10;
        $order = $this->request->param('order');//排序
        $content = $this->request->param('content');//输入内容搜索

        $offset = (($page-1) > 0) ? ($page-1) * $pageSize : 0;

        $userInfo = $this->auth->getUserinfo();

        if(empty($userInfo))
            $this->error(__('未登录'));

        if (empty($content))
            $this->error(__('搜索内容不能为空'));

        // $good = db('good')->where(function ($query) {
        //     $content = $this->request->param('content');//输入内容搜索

        //     $goodObj = new \app\api\model\Good();
        //     $where = array();
        //     $where['status'] = ['=', 1];
        //     $where['name'] = ['LIKE', '%' . $content . '%'];
        //   //  $where['columnId'] = ['=', 1];

        //     $brand = $goodObj->getList($where, '*', 1, 999);

        //     if (!empty($brand['list'])) {
        //         $ids = implode(',', array_column($brand['list'], 'id'));
        //         $query->where('brandId', 'in', $ids)->whereor('name', 'LIKE', '%' . $content . '%');
        //     } else {
        //         $where = array();
        //         $where['status'] = ['=', 1];
        //         $where['columnId'] = ['=', 1];

        //         $brand = $goodObj->getBrandList($where, '*', 1, 999);
        //         $ids = implode(',', array_column($brand['list'], 'id'));

        //         $query->where('brandId', 'in', $ids)->where('name', 'LIKE', '%' . $content . '%');
        //     }

        // })->where('status', '=', 0)->limit($offset, $pageSize)->order($order)->select();
       // $goodObj = new \app\api\model\Good();
        $where = array();
        $where['status'] = ['=', 0];
        $where['name'] = ['LIKE', '%' . $content . '%'];
        $good = db('good')->where($where)->limit($offset, $pageSize)->order($order)->select();


        if (!empty($good)) {
            foreach ($good as $key => $val) {
                $good[$key]['photo'] = empty($val['photo']) ? '' : config('img_url') . $val['photo'] . config('good_detail_style');
            }
        }

        //更新搜索日志
        $result = db('search_words')
            ->where('userId', '=', $userInfo['id'])
            ->where('word', '=', $content)
            ->find();

        if (!empty($result)) {
            db('search_words')
                ->where('userId', '=', $userInfo['id'])
                ->where('word', '=', $content)
                ->update(array('is_hide' => 0, 'updateTime' => time()));
        } else {
            db('search_words')
                ->insert(array(
                    'userId' => $userInfo['id'],
                    'word' => $content,
                    'is_hide' => 0,
                    'addTime' => time(),
                    'updateTime' => time()
                ));
        }

        $this->success('请求成功', array(
            'goodList' => empty($good) ? array() : $good
        ));
    }

    /**
     * 左导航
     */
    public function leftNavigation()
    {
        //获取今日秒抢品牌数据
        $goodObj = new \app\api\model\Good();
        $where = array();
        $where['status'] = ['=', 1];
        $where['endTime'] = ['>', time()];
        $where['startTime'] = ['<=', time()];
        $where['columnId'] = ['in', [1,3]];

        $result = $goodObj->getBrandList($where, '*', 1, 999);

        foreach ($result['list'] as $key => $val) {
            $result['list'][$key]['logo'] = empty($val['logo']) ? '' : $val['logo'] . config('navigation_style');
        }

        $this->success('请求成功', array('brandList' => $result['list']));
    }

    /**
     * 转发文本
     */
    public function shareTitle()
    {
        $userInfo = $this->auth->getUserinfo();

        if(empty($userInfo))
            $this->error(__('未登录'));

        $this->success('请求成功', array('title' => '来玩免单哦，大牌正品不要钱，真实好玩测人品，你敢来吗？'));
    }
}
