<?php

class ShakeAction extends CommonAction {
	public function index(){
        exit();
    }
	

	public function preview($shake_id=null){
		$obj = D('Weixin_shake');
		$objsn = D('Weixin_shakesn');
        if(!($shake_id = (int)$shake_id) && !($shake_id = $this->_post('shake_id'))){
			$this->Error('没有指定摇一摇ID');
        }else if(!$detail = $obj->find($shake_id)){
			$this->Error('没有指定摇一摇ID');
        }elseif(!$weixin =D('User_weixin')->where(array('shop_id'=>$detail['shop_id']))->select()){
			$this->error('该商家还未绑定公众号');
		}else{
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);
			$member = D('User')->find($weixin['user_id']);
			//$member =  K::M('member/weixin')->detail_by_openid($openid);

//$openid = 123456;
			import('ORG.Util.Page'); // 导入分页类 
			$filter['shake_id'] = $shake_id;
			$count = D('Weixin_shakeprize')->where($filter)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$prizes1 = D('Weixin_shakeprize')->where($filter)->select();
			if($prizes1){
                foreach ( $prizes1 as $k => $v ) {
                    $prizes[$v['id']] = $v;
				}
			}
            $this->assign('prizes',$prizes);
			$filter['openid'] = $openid;
            $filter['shop_id'] = $shop_id;

			$all_prizes1 = D('Weixin_shakesn')->where(array('shake_id'=>$shake_id))->order(array('sn_id' => 'desc'))->select();
			//$all_prizes = K::M('weixin/shakesn')->items(array('shake_id'=>$shake_id),array('sn_id'=>'DESC'));
			foreach ( $all_prizes1 as $k => $all ) {

				if ($all ['prize_id'] > 0) {
                    $all_prizes[$k] = $all;
                    $all_prizes[$k]['title'] = $prizes[$all['prize_id']]['title'];
                    $all_prizes[$k]['name'] = $prizes[$all['prize_id']]['name'];
					$all_prizes[$k]['photo'] = $prizes[$all['prize_id']]['photo'];
					$has [$all ['prize_id']] += 1; // 每个奖项已经中过的次数
					$new_shake [] = $all; // 最新中奖记录
					if($all ['openid'] == $openid){
						$shakesn [] = $all;
					} // 我的中奖记录
				} else {
					$no_count += 1; // 没有中奖的次数
				}
				
				// 记录我已抽奖的次数
				if($all ['openid'] == $openid){
					$my_count += 1;
				} 
			}
           foreach($all_prizes as $k => $v){
				if($v['openid'] == $openid){
					$my_zitems[$k] = $v;
				}
			}
          
            $this->assign('my_zitems',$my_zitems);
			//Import::L('weixin/jssdk.php');
            $jsSdk = $this->weixin_jssdk();
			//$jsSdk = new WeixinJSSDK($weixin['appid'], $weixin['secret']);
			$jsSdk1 = $jsSdk->getSignPackage();
			$this->assign('wxjscfg',$jsSdk1);
			//$this->pagedata['wxjscfg'] = $jsSdk->getSignPackage();
			$detail['wxjscfg'] = $jsSdk->getCardSignPackage($detail['id']);
			
			$detail['count'] = $detail['max_num'] - $my_count;
			$error = '';
			$stime = strtotime($detail ['stime']);
			$ltime = strtotime($detail ['ltime']);
			if ($ltime <= time ()) {
				$error = '活动已结束';
			} else if($stime >= time ()){
				$error = '活动还未开始';
			}else if ($detail['count']<=0) {
				$error = '您的摇一摇机会已用完啦';
			} else if ($detail['follower_condtion'] && $wx_info['subscribe'] == 0) {
				//
				switch ($detail ['follower_condtion']) {
					case 1 :
						$error = '关注后才能领取';
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				//
				$error = '用户注册后才能领取';
			} 
			//$this->pagedata['error'] = $error;
			$this->assign('error',$error);
			// 抽奖算法
			if(!$error){
				 $this->_lottery ( $detail, $prizes, $new_shake, $my_count, $has, $no_count );
			}
			$prizes_list = D('Weixin_shakeprize')->where(array('shake_id'=>$shake_id))->select();
			$this->assign('prizes_list',$prizes_list);
			//$this->pagedata['prizes_list'] = K::M('weixin/shakeprize')->items(array('shake_id'=>$shake_id));
			$this->assign('shakesn',$shakesn);
			$link1 = U('shake/set_sn_code');
            $this->assign('link1',$link1);
			$this->assign('all_prizes',$all_prizes);
			$this->assign('new_shake',$new_shake);
			$this->assign('detail',$detail);
			$this->assign('details', D('Shopdetails')->itemsByIds($shop_ids));//二维码图片
			//$this->pagedata['shakesn'] = $shakesn;
			//$this->pagedata['all_prizes'] = $all_prizes;
			//$this->pagedata['new_shake'] = $new_shake;
			//$this->pagedata['detail'] = $detail;
			$this->display();
			//$this->tmpl = 'weixin/shake/prev.html';
		}
	}

	// 抽奖算法 中奖概率 = 奖品总数/(预估活动人数*每人抽奖次数)
	function _lottery($data, $prizes, $new_prizes, $my_count = 0, $has = array(), $no_count = 0) {
		$max_num = empty ( $data ['max_num'] ) ? 1 : $data ['max_num'];
		$count = $data ['predict_num'] * $max_num; // 总基数
		                                                    // 获取已经中过的奖
		foreach ( $prizes as $p ) {
			$prizesArr [$p ['id']] = $p;
			
			$prize_num = $p ['num'] - $has [$p ['id']];
			for($i = 0; $i < $prize_num; $i ++) {
				$rand [] = $p ['id']; // 中奖的记录，同时通过ID可以知道中的是哪个奖
			}
		}
		
		if ($data ['predict_num'] != 1) {
			$remain = $count - count ( $rand ) - $no_count;
			$remain > 5000 && $remain = 5000; // 防止数组过大导致内存溢出
			for($i = 0; $i < $remain; $i ++) {
				$rand [] = 0; // 不中奖的记录
			}
		}
		if (empty ( $rand )) {
			$rand [] = - 1;
		}
		
		shuffle ( $rand ); // 所有记录随机排序
		$prize_id = $rand [0]; // 第一个记录作为当前用户的中奖记录
		$prize = array ();
		
		if ($prize_id > 0) {
			$prize = $prizesArr [$prize_id];
		} elseif ($prize_id == - 1) {
			$prize ['id'] = 0;
			$prize ['title'] = '奖品已抽完';
		} else {
			$prize ['id'] = 0;
			$prize ['title'] = '谢谢参与';
		}

		// 获取我的抽奖机会
		if (empty ( $data ['max_num'] )) {
			$prize ['count'] = 1;
		} else {
			$prize ['count'] = $max_num - $my_count - 1;
			$prize ['count'] < 0 && $prize ['count'] = 0;
		}
		if($max_num <= $my_count){
			$prize ['id'] = 0;
			$prize ['title'] = '您抽奖机会已经用完了';
		}
		$this->assign('prize',$prize);
		//$this->pagedata['prize'] = $prize;
	}

	function set_sn_code() {
		$scratch =D('Weixin_shake')->where(array('shake_id'=>$_POST['id']))->select();
		if(empty($openid)){
			$openid = $this->access_openid($scratch['shop_id']);
		}
		$client = $this->wechat_client($scratch['shop_id']);
		$wx_info = $client->getUserInfoById($openid);
		$weixin =D('User_weixin')->where(array('openid'=>$openid))->select();
		$member = D('User')->find($weixin['user_id']);
		//$member =  K::M('member/weixin')->detail_by_openid($openid);

		if($_POST['id']){
			$data ['sn'] = uniqid ();
			$data ['uid'] = $member['user_id'];
			$data['shake_id'] = $_POST['id'];
			$data['openid'] = $openid;
			$data['nickname'] = $wx_info['nickname'];						$data['img'] = $wx_info['headimgurl'];
			$data ['prize_id'] = $_POST['prize_id'];
            $data ['dateline'] = time();
			if(!empty($data['shake_id'])) {
				$scratch =D('Weixin_shake')->where(array('shake_id'=>$data['shake_id']))->select();
				//$scratch = K::M('weixin/shake')->detail($data ['shake_id']);
				$data['shop_id'] = $scratch['shop_id'];
			}
			$title = '';
			if (! empty ( $data ['prize_id'] )) {
				$scratch =D('Weixin_shakeprize')->find($data['prize_id']);
				//$items = K::M('weixin/shakeprize')->detail($data ['prize_id']);
			}
			$data['prize_title'] = $items['title'];
			D('Weixin_shakesn')->add($data);
         
			echo $res;
		}
	}
}