<?php

    /**
     * 充值会员
     */
    public function rechargeMember()
    {
        $recharge_type = $this->request->post('recharge_type/d', 0, 'intval');

        // 开启事务
        Db::startTrans();
        try{

            $model_recharge = new \app\common\model\UserRecharge();

            /* 充值会员：充值订单入库 begin*/
            $recharge_status = $model_recharge->rechargeMember($this->auth->getUserID(), $recharge_type);
            if ($recharge_status === false) {
                throw new \Exception($model_recharge->getError());
            }
            /* 充值会员：充值订单入库 end*/

            // 修改会员信息
            $user = $this->auth->getUser();
            $user_edit = [];

            /*根据充值类型，进行不同的操作 begin*/
            if ($recharge_type === $model_recharge::RECHARGE_TYPE_CONSUMER) {   // 如果充值类型是消费者会员，检查必要字段
                $apply_user = $this->request->post('apply_user/a');
                if (!$apply_user) {
                    throw new \Exception('请填写会员数据');
                }
                
                // 增加会员修改信息
                $user_edit['xingming'] = isset($apply_user['xingming']) ? $apply_user['xingming'] : '';
                $user_edit['mobile']   = isset($apply_user['mobile']) ? $apply_user['mobile'] : '';

            } elseif ($recharge_type === $model_recharge::RECHARGE_TYPE_MERCHANT) {   // 如果充值类型是商家会员，增加商家数据

                $apply_store = $this->request->post('apply_store/a');
                if (!$apply_store) {
                    throw new \Exception('请填写商家数据');
                }

                // 增加会员修改信息
                $user_edit['xingming'] = isset($apply_store['shopkeeper_name']) ? $apply_store['shopkeeper_name'] : '';
                $user_edit['mobile']   = isset($apply_store['store_mobile']) ? $apply_store['store_mobile'] : '';

                $model_store = new \app\common\model\Store();
                $store = $model_store::withTrashed()->where('user_id', $recharge_status['user_id'])->find();
                
                if (!$store) { // 不存在店铺数据，新增店铺
                    // 添加必要元素
                    $apply_store['user_id'] = $this->auth->getUserID();
                    $apply_store['recharge_id'] = $recharge_status['id'];
                    $apply_store['pay_status'] = $model_store::PAY_STATUS_NOT;
                    $apply_store['audit_status'] = $model_store::AUDIT_STATUS_NOT;

                    $store_status = $model_store->validate('\app\common\validate\Store.add')->save($apply_store);
                    if (!$store_status) {
                        throw new \Exception($model_store->getError());
                    }
                } else {
                    // 如果店铺状态是付款成功，且未被删除
                    if (($store->pay_status === $model_store::PAY_STATUS_SUCCEED) && !$store->delete_time) {
                        throw new \Exception('您已经存在一个店铺，请不要重复添加');
                    }

                    // 添加必要元素
                    $apply_store['id'] = $store->id;
                    $apply_store['user_id'] = $this->auth->getUserID();
                    $apply_store['recharge_id'] = $recharge_status['id'];
                    $apply_store['pay_status'] = $model_store::PAY_STATUS_NOT;
                    $apply_store['audit_status'] = $model_store::AUDIT_STATUS_NOT;
                    $apply_store['delete_time'] = null;

                    $store_status = $store->validate('\app\common\validate\Store.edit_add')->isUpdate(true)->save($apply_store);
                    if (!$store_status) {
                        throw new \Exception($store->getError());
                    }
                }

            }

            // 修改会员信息
            $user_status = $user->validate('\app\common\validate\User.recharge_member')->save($user_edit);
            if (!$user_status) {
                throw new \Exception($user->getError());
            }

            /*根据充值类型，进行不同的操作 end*/

            /*吊起相应支付接口 begin*/
            // 实例化 easywechat 接口
            $app = new Application(config('easywechat'));

            // 实例化 下单接口
            $attributes = [
                'trade_type'       => 'JSAPI', // JSAPI，NATIVE，APP...
                'body'             => '用户充值会员',
                'detail'           => '用户充值会员',
                'out_trade_no'     => $recharge_status['order_code'],
                'total_fee'        => $recharge_status['recharge_money'] * 100, // 单位：分
                'notify_url'       => $this->request->domain() . '/api/Mine/rechargeCallback', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'openid'           => $this->auth->getUserInfo()['wechat_openid'], // trade_type=JSAPI，此参数必传，用户在商户appid下的唯一标识，
            ];
            $order = new Order($attributes);
            $result = $app->payment->prepare($order);
            if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS')
            {
                 $ajax_data=[
                     // 'code_url'           =>  $result->code_url,
                     'brand_wcpayrequest' =>  $app->payment->configForPayment($result->prepay_id),
                     'out_trade_no'       =>  $recharge_status['order_code'],
                     'price'              =>  $recharge_status['recharge_money'],
                     'give_integral'      =>  $recharge_status['give_integral']
                 ];

            }else{
                throw new \Exception('生成订单错误！');
            }
            /*吊起相应支付接口 end*/

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error($e->getMessage());
        }

        $this->success('请求成功', $ajax_data);

    }

    /**
     * 充值会员回调
     * @return bool / string
     */
    public function rechargeCallback()
    {
        $app = new Application(config('easywechat'));
        $response = $app->payment->handleNotify(function($notify, $successful) {
            $order_arr = json_decode($notify, true);
            $order_code = $order_arr['out_trade_no'];//订单号
            // 查找订单数据
            $model_recharge = new \app\common\model\UserRecharge();
            $order = $model_recharge->get(['order_code' => $order_code]);

            if (!$order) { // 如果订单不存在
                return true;
                // return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }

            // 如果订单存在
            // 检查订单是否已经更新过支付状态
            if ($order->recharge_status === $model_recharge::RECHARGE_STATUS_SUCCEED) { // 假设订单字段“支付时间”不为空代表已经支付
                return true; // 已经支付成功了就不再更新了
            }

            // 用户是否支付成功
            if ($successful) {
                // 将支付状态修改为已经支付状态
                $order->recharge_status = $model_recharge::RECHARGE_STATUS_SUCCEED;
                $order->pay_time = time(); // 更新支付时间为当前时间
            } else { // 用户支付失败
                $order->recharge_status = $model_recharge::RECHARGE_STATUS_FAILURE;
            }

            // 开启事务
            Db::startTrans();
            $error_info = '';
            try{
                // 记录日志变量
                $log_info = '订单数据：' . json_encode($order) . '，微信回调数据为:' . $notify;

                // 保存订单
                $order->save();

                // 如果充值类型是商家店铺，修改对应的店铺付款状态
                if ($order->recharge_type === $model_recharge::RECHARGE_TYPE_MERCHANT) {
                    $model_store = new \app\common\model\Store();
                    $store = $model_store::withTrashed()->where('user_id', $order->user_id)->find();
                    if ($store) {
                        // 用户是否支付成功
                        if ($successful) {
                            // 将支付状态修改为已经支付状态
                            $store->pay_status = $model_store::PAY_STATUS_SUCCEED;
                            $store->audit_status = $model_store::AUDIT_STATUS_COURSE;
                        } else { // 用户支付失败
                            $store->pay_status = $model_store::PAY_STATUS_FAILURE;
                        }
                        $store->save();
                    }
                }

                if ($successful) { // 支付成功
                    // 修改用户所属组别
                    $this->model->group($order->buy_group, $order->user_id);

                    // 增加用户积分
                    $memo = '用户购买' . $order->recharge_type_text . '，赠送' . $order->give_integral . '积分';
                    $change_type = \app\common\model\ScoreLog::CHANGE_TYPE_RECHARGE;
                    $this->model->score($order->give_integral, $order->user_id, $change_type, $order->id, $memo);
                }

                // 提交事务
                Db::commit(); 
            } catch (\Exception $e) {
                $error_info = '错误信息: ' . $e->getMessage();
                // 回滚事务
                Db::rollback();
            } finally {
                // 记录日志
                $log_info = ($error_info ? '[error]:' : '[succeed]:') . $log_info . $error_info;
                Log::write($log_info, 'recharge');
            }

            return true; // 返回处理完成
        });
        
        return $response;
    }