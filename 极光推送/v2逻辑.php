<?php

namespace app\admin\controller\jg;
//require 'path_to_sdk/autoload.php';

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class History extends Backend
{
    
    /**
     * History模型对象
     * @var \app\admin\model\jg\History
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\jg\History;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
      /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            foreach($list as &$lt){
                $lt['status'] = $lt['status'] == 1 ? '发送成功' : '发送失败';
                $lt['platform'] = $lt['platform'] == 1 ? 'Android' : 'IOS';
                $lt['msg_type'] = $lt['msg_type'] == 1 ? '通知消息' : '自定义消息';    
            }
            //查询管理员表
            $admin_info = db('admin')->select();

            foreach($list as  &$lis){
                foreach($admin_info as $ai ){
                    if($lis['user_id'] == $ai['id']){
                        $lis['user_id'] = $ai['username'];
                    }
                }
            }

            //查询产品
            $loan_info = db('loan')->select();
            foreach($list as  &$lsv){
                foreach($loan_info  as $lo){
                    if($lsv['loan_id'] == $lo['id']){
                        $lsv['loan_id'] = $lo['loan_name'];
                    }
            }
        }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
    * 生成随机的编号
    */
    public function  create_order($num = 3){
        $strand = (double)microtime() * 1000000;
        if(strlen($strand)<$num){
            $strand = str_pad($strand,$num,"0",STR_PAD_LEFT);
        }
        return date('Y').$strand;
    }
    public function imgUrl($img)
    {
        return empty($img) ? '' : config('web_host').$img;
    }

     /**
     * 添加
     * 消息入库并发送消息
     */
    public function send()
    {   
        if ($this->request->isPost()) {
            //接口所有的参数
            $infos = $this->request->param();
            $content = $infos['row']['content'];
            $platform = $infos['row']['platform'];
            $msg_type = $infos['row']['msg_type'];
            $tar_type = $infos['row']['tar_type'];
            $loan_id = $infos['row']['loan_id'];
            $user_id = $_SESSION['think']['admin']['id'];
            $sendno = $this->create_order();
            $data = [
                'user_id' => $user_id, 
                'sendno' => $sendno,
                'content' => $content,
                'platform' => $platform,
                'msg_type' => $msg_type,
                'tar_type' => $tar_type,
                'time' => time(),
                'loan_id'=> $loan_id,
            ];
            //入库
            $id = db('jg_history')->insertGetId($data);
            if(!$id){
                $this->error('发送失败,请重新尝试');
            }
            //调用极光发送方法
            //$n_title   =  '测试';//发送的标题,通知标题。不填则默认使用该应用的名称。只有 Android 支持这个参数。
            $n_content = $content;//通知内容。

            //自定义参数
             //通知附加参数。JSON 格式。客户端可取得全部内容。
            //根据loan_id 查询是否是url 还是 透传产品 , 透传要携带url ,并获取 产品名
            $loans = db('loan')->where('id',$loan_id)->field('id,loan_name,link,url,status')->find();
            $loan_name = $loans['loan_name'];
            $url = $this->imgUrl($loans['url']);    
            $link = $loans['link'];
            $params = array('loan_id'=>$loan_id,'loan_name'=>$loan_name,'link'=>$link,'url'=>$url);
           
            // $loan_id = array(); //产品id
            //$arr = array('fromer'=>'发送者','fromer_name'=>'发送者名字','fromer_icon'=>'发送者头像','image'=>'发送图片链接','sound'=>'发送音乐链接', 'pageType'=>0);//自定义参数
               
            $appkeys = config('jg.appkey');

            $masterSecret = config('jg.MasterSecret');

            //编号
            $sendno = $sendno;//发送编号。由开发者自己维护，标识一次发送请求
            //接收者类型
            $receiver_type = 4; //对所有的用户推送
            $receiver_value = 4;//发送范围值，与 receiver_type相对应,4表示不需要填
            //发送消息类型
            // $msg_type = $msg_type;
            $msg_type = 1;
            //平台
            $platform  =  $platform == 1 ? 'android' : 'ios'; 
            $platform = $platform ;//目标终端类型，如果是全平台得话可直接写成“all”，如果是安卓和苹果应用逗号隔开（'android,ios'）

            
           //$msg_content = json_encode(array('n_builder_id'=>0, 'n_title'=>$n_title,'n_content'=>$n_content,'n_extras'=>$arr));
            $msg_content = json_encode(array('n_builder_id'=>990,'n_content'=>$n_content,'n_extras'=>$params));
            $obj = new Sendmsg($masterSecret,$appkeys);
            $res = $obj->send($sendno, $receiver_type, $receiver_value, $msg_type, $msg_content, $platform);
            //改变状态值
            if($res['sendno']== $sendno && $res['errmsg']=="Succeed"){
                db('jg_history')->where('sendno',$res['sendno'])->update(['status'=>1]);
            }
            exit();
        }
        return $this->view->fetch();

    }
}
