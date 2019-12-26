<?php


class CouponAction extends CommonAction {
	
	public function _initialize() {
        parent::_initialize();
		if($this->workers['coupon'] != 1){
          $this->error('对不起，您无权限，请联系掌柜开通');
        }
		
    }
	
	public function lists() {
        $aready = (int) $this->_param('aready');
		$this->assign('aready', $aready);
		$this->display();
	}

	public function couponloading() {
		$Coupondownloads = D('Coupondownload');
		import('ORG.Util.Page');
		$map = array('user_id' => $this->uid);
                
                $aready = (int) $this->_param('aready');

		if ($aready == 2) {
			$map['is_used'] = array('egt',1);
		}elseif ($aready == 1) {
			$map['is_used'] = 0;
                }else{
                    $aready == null;
                }
                
		$count = $Coupondownloads->where($map)->count();
		$Page = new Page($count, 25);
		$show = $Page->show();
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page->totalPages < $p) {
			die('0');
		}
	
		$list = $Coupondownloads->where($map)->order('is_used asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$coupon_ids = $shop_ids = array();
		foreach ($list as $k => $val) {
			$coupon_ids[$val['coupon_id']] = $val['coupon_id'];
			$shop_ids[$val['shop_id']] = $val['shop_id'];
		}
		$shops = D('Shop')->itemsByIds($shop_ids);
		$coupon = D('Coupon')->itemsByIds($coupon_ids);
		$this->assign('coupon', $coupon);
		$this->assign('shops', $shops);
		$this->assign('list', $list);
		$this->assign('page', $show);	
		$this->display();
	}

	public function coupondel($download_id) {
		$download_id = (int) $download_id;
		if (empty($download_id)) {
			$this->error('该优惠券不存在');
		}
		if (!$detail = D('Coupondownload')->find($download_id)) {
			$this->error('该优惠券不存在');
		}
		if ($detail['shop_id'] != $this->shop_id) {
			$this->error('非法操作');
		}
		
		
		D('Coupondownload')->delete($download_id);
		$this->success('删除成功！', U('coupon/lists'));
	}
	
	
    public function weixin() {
        $download_id = $this->_get('download_id');
        if (!$detail = D('Coupondownload')->find($download_id)) {
            $this->error('没有该优惠券');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error("非法操作");
        }
        if ( $detail['is_used'] != 0) {
            $this->error('该优惠券属于不可消费的状态');
        }
        $url = U('/worker/weixin/coupon', array('download_id' => $download_id, 't' => NOW_TIME, 'sign' => md5($download_id . C('AUTH_KEY') . NOW_TIME)));
	
        $token = 'couponcode_' . $download_id;
        $file = baoQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	

    public function index() {
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            foreach ($code as $v) {
                if (empty($v)) {
					$this->error('请输入电子优惠券');
                }
            }
            $obj = D('Coupondownload');
            $ip = get_client_ip();
            $return = array();
            foreach ($code as $key => $var) {
                $var = trim(htmlspecialchars($var));
                if (!empty($var)) {
                    $data = $obj->find(array('where' => array('code' => $var)));
                    if (!empty($data) && (int) $data['shop_id'] == $this->shop_id && (int) $data['is_used'] == 0) {
                        if (false !== $obj->save(array('download_id' => $data['download_id'], 'is_used' => 1, 'used_ip' => $ip, 'used_time' => NOW_TIME))) {
                            $return[$var] = $var;
                        }
                    } else {
                        continue;
                    }
                }
            }
            if (!empty($return)) {
                $msg = join(',',$return);
                $this->error("恭喜您，您成功消费的优惠券如下：".$msg); //放入foreach内循环一次后便会退出
            }else{
				$this->error('无效的电子优惠券');
 
            }
        } else {
            $this->display();
        }
    }

//         if($this->isPost()){
//            $code=$this->_post('code',false); 
//	
//            if(empty($code)){
//				$this->error('请输入电子优惠券');
//                exit('<script>parent.used("请输入电子优惠券！");</script>');
//            }
//            $obj =  D('Coupondownload');
//			
//            $return = array();
//            $ip = get_client_ip();
//            foreach($code  as $var){
//                if(!empty($var)){
//                    $data =$obj->find(array('where'=>array('code'=>$var)));
//                    if(!empty($data) && $data['shop_id'] == $this->shop_id && $data['is_used'] == 0 ){
//                      $obj->save(array('download_id'=>$data['download_id'],'is_used'=>1,'used_time'=>NOW_TIME,'used_ip'=>$ip));
//                      $return[$var] = $var;
//                    }
//                }
//            }   
//            if(empty($return)){
//				$this->error('请输入电子优惠券');
//                exit('<script>parent.used("没有可消费的电子优惠券！");</script>');
//				//$this->error("没有可消费的电子优惠券！");
//            }
//            if(NOW_TIME - $this->shop['ranking'] < 86400){ //更新排名
//                D('Shop')->save(array('shop_id'=>  $this->shop_id,'ranking'=>NOW_TIME));
//            }
//			$this->error('恭喜您，您成功消费的优惠券如下');
//            //echo '<script>parent.used("恭喜您，您成功消费的优惠券如下："+"'.join(',',$return).'");</script>';
//        }else{
//            $this->display();
//        }       
}
