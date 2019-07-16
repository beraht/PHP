<?php
namespace app\bis\controller;
use think\Controller;
class Login extends Controller{
/**
 * get 请求时,走的是else,检测是否已经登入
 * post 请求时走post
 * @return [type] [description]
 */
	public function index(){
		if(Request()->isPost()){
			$data = input('post.');
			//数据校验
			
			$BisDatau =  model('BisAccount')->where('username',$data['username'])->find()->toArray();

			if(!$BisDatau){
				show('0','error','账户错误');
			}
			if(md5($data['password']) != $BisDatau['password']){
				show('0','error','密码错误');
			}

			model('BisAccount')->where('username',$data['username'])->update(['last_login_time'=>time()]);

			session('bisAccount',$BisDatau,'bis');
			show('1','ok','登入成功');
			
		}else{
			//进入这个页面时,发现已经登入
			// 获取session
            $account = session('bisAccount', '', 'bis');
            if($account) {
                exit("<script> setTimeout(function(){
                    location.href = '/index.php/bis/index/index';
                },1000);</script>");
            }
			return $this->fetch();

		}
		
	}

}