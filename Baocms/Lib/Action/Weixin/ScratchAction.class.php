<?php

class ScratchAction extends CommonAction {

	public function index()
    {
        exit();
    }
	
	public function show($scratch_id) {
		$obj = D('Weixin_scratch');
		$objsn = D('Weixin_scratchsn');
		if(!($scratch_id = (int)$scratch_id) && !($scratch_id = $this->_post('scratch_id'))){
			$this->Error('没有指定刮刮乐ID');
        }else if(!$detail = $obj->find($scratch_id) ){
			$this->Error('该刮刮乐不存在或已经删除');
        }else{
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);	
			$objm =  D('User_weixin');
			$obju =  D('User');
			$map = array();
			$map['openid'] = $openid;
			
			if($uid = $objm->where($map)->getField('user_id')){
				$member = $obju->find($uid);
			}else{
				$this->redirect("Scratch/show",array('scratch_id'=>$scratch_id));
				$this->Error('微信参数错误');
			}

			//$member =  K::M('member/weixin')->detail_by_openid($openid);
//$openid ='111111';
			$objm =  D('User_weixin');
			$obju =  D('User');
			$map = array();
			$map['openid'] = $openid;
			
			if($uid = $objm->where($map)->getField('user_id')){
				$member = $obju->find($uid);
			}else{
				$this->Error('微信参数错误');
			}
			
	
			$objp =  D('Weixin_prize');
			$filter['scratch_id'] = $scratch_id;
			import('ORG.Util.Page'); // 导入分页类
			$count = $objp->where($map)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$prizes = $objp->where($filter)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($prizes){
				$this->assign('prizes',$prizes);
			}

			//$filter['openid'] = $openid;
			$all_prizes = $objsn->where(array('scratch_id'=>$scratch_id))->order(array('sn_id' => 'desc'))->select();
			foreach ( $all_prizes as $all ) {
				if ($all ['prize_id'] > 0) {
					$has [$all ['prize_id']] += 1; // 每个奖项已经中过的次数
					$new_scratch [] = $all; // 最新中奖记录
					if($all ['openid'] == $openid){
						$scratchsn [] = $all;
					} // 我的中奖记录
				} else {
					$no_count += 1; // 没有中奖的次数
				}
				
				// 记录我已抽奖的次数
				if($all ['openid'] == $openid){
					$my_count += 1;
				} 
			}
			$detail['count'] = $detail['max_num'] - $my_count;
			$error = '';
			$stime = strtotime($detail ['stime']);
			$ltime = strtotime($detail ['ltime']);
			if ($detail ['ltime'] <= time ()) {
				$error = '活动已结束';
			} else if($detail ['stime'] >= time ()){
				$error = '活动还未开始';
			}else if ($detail['count']<=0) {
				$error = '您的刮卡机会已用完啦';
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

			$this->assign('error',$error);



			// 抽奖算法
			if(!$error){
				 //$this->_lottery ( $detail, $prizes, $new_scratch, $my_count, $has, $no_count );
			}

			//抽奖详情
			if(!$sdetail = $obj->find($scratch_id)) {
				$this->Error('该刮刮乐不存在或已经删除');
			}else{
				$this->assign('sdetail',$sdetail);
			}
			//print_r($sdetail);exit;
			//奖项详情
            //抽奖详情
			$filter = array();
			$filter['scratch_id'] = $scratch_id;
			$items1 = $objp->where(array('scratch_id'=>$scratch_id))->select();
			if($items1){
				$uids = '';
				foreach($items1 as $k => $v){
					$items[$v['id']] = $v;
				}
			}
			$this->assign('items',$items);
			

			//中奖详情
			$filter = $pager = array();
			$pager['page'] = max(intval($page), 1);
			$pager['limit'] = $limit = 50;
					if($SO = $this->_post('SO')){
				$pager['SO'] = $SO;
				
			}
			$filter['scratch_id'] = $scratch_id;
			$zitems = $objsn->where($filter)->order(array('sn_id' => 'desc'))->select();
			
			if($zitems){
				$uids = '';
				foreach($zitems as $k => $v){
					$uids[$v['uid']] = $v['uid'];
					$zitems[$k]['title'] = $items[$v['prize_id']]['title'];
                    $zitems[$k]['name'] = $items[$v['prize_id']]['name'];
					$zitems[$k]['photo'] = $items[$v['prize_id']]['photo'];
				}
				$data = array();
				$data['user_id']=array('in',$uids);
				$member_list = D('User')->where($data)->select();
				$this->assign('member_list',$member_list);
			}
			$this->assign('zitems',$zitems);
			$this->assign('zdetail',$zdetail);

			//我的中奖信息
			//$openid = $this->cookie->get('wx_openid')
			//$openid = 111111;
			$myzitems = '';
			foreach($zitems as $k => $v){
				if($v['openid'] == $openid){
					$myzitems[$k] = $v;
				}
			}
			$this->assign('myzitems',$myzitems);
			//print_r($myzitems);exit;
			$link =  U('scratch/lottery', array('scratch_id' => $scratch_id));
			$link1 = U('scratch/set_sn_code');
			
		/*	$link = K::M('helper/link')->mklink('weixin/scratch/lottery', array(), array(), 'base');
			$link1 = K::M('helper/link')->mklink('weixin/scratch/set_sn_code', array(), array(), 'base');
			$this->pagedata['link'] = $link;
			$this->pagedata['link1'] = $link1;*/
			$this->assign('details', D('Shopdetails')->itemsByIds($shop_ids));//二维码图片
			$this->assign('link',$link);
			$this->assign('link1',$link1);
			$this->assign('scratchsn',$scratchsn);
			$this->assign('new_scratch',$new_scratch);
			$this->assign('detail',$detail);
			$this->display();
		}
		
	}

	// 抽奖算法 中奖概率 = 奖品总数/(预估活动人数*每人抽奖次数)
	function lottery($scratch_id) {
		
		$prize = array ();
		if(!($scratch_id) && !($scratch_id = $this->_post('id'))){
           
            $prize ['id'] = 0;
			$prize ['title'] = '刮刮乐不存在';
        }elseif(!$data = D('Weixin_scratch')->find($scratch_id)){
 			$prize ['id'] = 0;
			$prize ['title'] = '刮刮乐不存在';

        }else{
			$filter['scratch_id'] = $scratch_id;
			$prizes = D('Weixin_prize')->where(array('scratch_id'=>$scratch_id))->select();
			$all_prizes = D('Weixin_scratchsn')->where(array('scratch_id'=>$scratch_id))->order(array('sn_id' => 'desc'))->select();

//$openid =111111;
			if(empty($openid)){
							$openid = $this->access_openid($data['shop_id']);
						}
			foreach ( $all_prizes as $all ) {
				if ($all ['prize_id'] > 0) {
					$has [$all ['prize_id']] += 1; // 每个奖项已经中过的次数
					$new_prizes [] = $all; // 最新中奖记录
					if($all ['openid'] == $openid){
						$scratchsn [] = $all;
					} // 我的中奖记录
				} else {
					$no_count += 1; // 没有中奖的次数
				}
				
				// 记录我已抽奖的次数
				if($all ['openid'] == $openid){
					$my_count += 1;
				} 
			}

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
			echo $prize['id'].'|'.$prize['title'].'|'.$prize['count'].'|'.$prize['name'];
			exit;
			//$this->pagedata['prize'] = $prize;
		}
	}

	function set_sn_code() {
		$scratch = D('weixin_scratch')->find($_POST['id']);
		if(empty($openid)){
			$openid = $this->access_openid($scratch['shop_id']);
		}
		$client = $this->wechat_client($scratch['shop_id']);
		$wx_info = $client->getUserInfoById($openid);
//$openid =111111;
		$member = D('User_weixin')->where(array('openid',$openid))->select();
		$user =  D('User')->find($member['user_id']);

		if(!$_POST['id']){
			 $this->err->add('数据出错', 212);
		}else{
			$data ['sn'] = uniqid ();
			$data ['user_id'] = $member['user_id'];
			$data['scratch_id'] = $_POST['id'];
			$data['openid'] = $openid;
			$data ['prize_id'] = $_POST['prize_id'];
			$data['dateline'] = time();
            $data['nickname'] =$wx_info['nickname'];						
			$data['img'] = $wx_info['headimgurl'];
			if (! empty ( $data ['scratch_id'] )) {
				$scratch = D('weixin_scratch')->find($data ['scratch_id']);

				$data['shop_id'] = $scratch['shop_id'];
			}
			$title = '';
			if (! empty ( $data ['prize_id'] )) {
				$items = D('weixin_prize')->find($data ['prize_id']);
			}
			$data ['prize_title'] = $items['title'];
			D('weixin_scratchsn')->add($data);
			echo $res;
		}
	}
}