<?php
class TuanAction extends CommonAction
{
    public function _initialize()
    {
        parent::_initialize();
        $this->tuancates = D('Tuancate')->fetchAll();
        $this->assign('tuancates', $this->tuancates);
        $branch = D('Shopbranch')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 1))->select();
        $this->assign('branch', $branch);
        if ($this->workers['tuan'] != 1) {
            $this->error('对不起，您无权限，请联系掌柜开通！！！');
        }
    }
    public function order(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
        // 输出模板
    }
    public function orderload()
    {
        $Tuanorder = D('Tuanorder');
        import('ORG.Util.Page');// 导入分页类
        $map = array('shop_id' => $this->shop_id, 'closed' => 0);//这里只显示 实物
        $aready = (int) $this->_param('aready');
        if ($aready == 1) {
            $map['status'] = 1;
        } elseif ($aready == 0) {
            $map['status'] = 0;
        } elseif ($aready == 3) {
            $map['status'] = 3;
        } elseif ($aready == 4) {
            $map['status'] = 4;
        } elseif ($aready == 8) {
            $map['status'] = 8;
        }elseif ($aready == 2) {
            $map['status'] = -1;
        } else {
            $map['status'] = 0;
        }
        $count = $Tuanorder->where($map)->count();// 查询满足要求的总记录数
        $Page = new Page($count, 10);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tuanorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $tuan_ids = array();
        foreach ($list as $k => $val) {
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }//查询商家
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));//查询商家名字
        $this->assign('list', $list);// 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->display();// 输出模板
    }
    public function detail($order_id)
    {
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Tuanorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('请不要操作其他店铺的订单');
        }
        if (!($dianping = D('Tuandianping')->where(array('order_id' => $order_id, 'user_id' => $this->uid))->find())) {
            $detail['dianping'] = 0;
        } else {
            $detail['dianping'] = 1;
        }
        $this->assign('tuans', D('Tuan')->find($detail['tuan_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function used(){
        $counts['tuan_order_code_is_used'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count(); //未验证
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            $c = 0;
            foreach ($code as $k => $v) {
                if ($v) {
                    $c = $c + 1;
                }
            }
            if (empty($c)) {
                $this->fengmiMsg('请输入套餐码!');
            }
            $obj = D('Tuancode');
            $shopmoney = D('Shopmoney');
            $return = array();
            $ip = get_client_ip();
            if (count($code) > 10) {
                $this->fengmiMsg('一次最多验证10条套餐码！');
            }
            $userobj = D('Users');
            foreach ($code as $key => $var) {
                $var = trim(htmlspecialchars($var));
                if (!empty($var)) {
                    $data = $obj->find(array('where' => array('code' => $var)));
                    if (!empty($data) && $data['shop_id'] == $this->shop_id && (int) $data['is_used'] == 0 && (int) $data['status'] == 0) {
						
						 //解决了多多份套餐无法点评的BUG
                            $Tuancode_count = $obj->where(array('order_id' => $data['order_id'], 'is_used' => 0))->count();
                            if ($Tuancode_count == 1) {
                                D('Tuanorder')->save(array('order_id' => $data['order_id'], 'status' => 8));
                                //套餐状态修改为8
                            }
							
                        if ($obj->save(array(
							'code_id' => $data['code_id'], 
							'is_used' => 1, 
							'used_time' => NOW_TIME, 
							'worker_id' => $this->uid, 
							'used_ip' => $ip
						))){ //这次更新保证了更新的结果集
                           D('Sms')->tuan_TZ_user($data['code_id']);//发短信，先发再处理逻辑
                            
                            if (!empty($data['price'])) {
                                $data['intro'] = '套餐消费' . $data['order_id'];
                                $shop = D('Shop')->find($data['shop_id']);
                                $shopmoney->add(array(
									'shop_id' => $data['shop_id'], 
									'city_id' => $shop['city_id'], 
									'area_id' => $shop['area_id'], 
									'branch_id' => $data['branch_id'], 
									'money' => $data['settlement_price'], 
									'create_ip' => $ip, 
									'create_time' => NOW_TIME, 
									'order_id' => $data['order_id'], 
									'intro' => $data['intro']
								));
                                D('Users')->Money($shop['user_id'], $data['settlement_price'], '商户套餐资金结算:' . $data['order_id']);//商户资金增加
                                $return[$var] = $var;
                                D('Users')->gouwu($data['user_id'], $data['price'], '套餐码消费成功');
								//套餐返还积分给商家用户
								if(!empty($data['real_integral'])){
									$config = D('Setting')->fetchAll();
									if($config['integral']['tuan_return_integral'] == 1){
										D('Users')->return_integral($shop['user_id'], $data['real_integral'] , '套餐用户消费积分返还给商家');
									}
								}
                                $this->fengmiMsg($key . '验证成功!', U('tuan/used'));
                            } else {
                                $this->fengmiMsg($key . '到店付套餐码验证成功!', U('tuan/used'));
                            }
                        }
                    } else {
                        $this->fengmiMsg($key . 'X该套餐码无效!', U('tuan/used'));
                    }
                }
            }
        } else {
            $this->assign('counts', $counts);
            $this->display();
        }
    }
    public function usedok()
    {
        $Tuancode = D('Tuancode');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('shop_id' => $this->shop_id, 'is_used' => '1', 'worker_id' => $this->uid);
        if (strtotime($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && strtotime($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            if (!empty($bg_time) && !empty($end_date)) {
                $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            }
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                if (!empty($bg_time)) {
                    $map['create_time'] = array('EGT', $bg_time);
                }
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                if (!empty($end_time)) {
                    $map['create_time'] = array('ELT', $end_time);
                }
                $this->assign('end_date', $end_date);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $keyword = intval($keyword);
            if (!empty($keyword)) {
                $map['code'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
        }
        $count = $Tuancode->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $list = $Tuancode->where($map)->order(array('used_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $user_ids[$val['user_id']] = $val['user_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show);// 赋值分页输出
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
        $this->display();// 输出模板
    }
}