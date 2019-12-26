<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class RelayAction extends CommonAction {

	public function index()
    {
        exit();
    }
	
	public function preview($relay_id=null)
	{
		$obj = D('Weixin_relay');
		$objsn = D('Weixin_relaysn');
        if(!($relay_id = (int)$relay_id) && !($relay_id = $this->GP('relay_id'))){
            $this->Error('未指定要修改的内容ID');
        }else if(!$detail = $obj->find($relay_id)){
			$this->Error('该接力不存在或已经删除');
        }elseif(!$weixin =D('Shopdetails')->where(array('shop_id'=>$detail['shop_id']))->find()){
			$this->Error('该商家还未绑定公众号');
		}else{
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);
			$stime = strtotime($detail ['s_time']);
			$ltime = strtotime($detail ['l_time']);
	//$openid = 111111;
			$list =$objsn->where(array('relay_id'=>$relay_id,'openid'=>$openid))->select();
			foreach($list as $k => $v){
				$my_sn_list = $v;
				$this->assign('my_sn_list',$v);
				//$this->pagedata['my_sn_list'] = $my_sn_list = $v;
			}
			$aa = $obj->find($relay_id);
			$bb['views'] = $aa['views'] +1;
			$bb['relay_id'] = $aa['relay_id'];
			$obj->save($bb);
			
            $jsSdk = $this->weixin_jssdk($weixin['app_id'], $weixin['app_key']);
			$jsSdk1 = $jsSdk->getSignPackage();
			
			$this->assign('wxjscfg',$jsSdk1);
			//$this->pagedata['wxjscfg'] = $jsSdk->getSignPackage();
			$this->assign('detail',$detail);
			//$this->pagedata['detail'] = $detail;
			$condition = array ();

			$filter['relay_id'] = $relay_id;
			$prizes = D('Weixin_relayprize')->where($filter)->select();
			if($prizes){
				$this->assign('prizes',$prizes);
				//$this->pagedata['prizes'] = $prizes;
			}
			import('ORG.Util.Page'); // 导入分页类
			$count = $objsn->where($filter)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$sn_list = $objsn->where($filter)->order(array('gold' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($sn_list){
				$this->assign('sn_list',$sn_list);
				//$this->pagedata['sn_list'] = $sn_list;
			}
			$Page = array();
			import('ORG.Util.Page'); // 导入分页类
			$count = $objsn->where($filter)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$filter['openid'] = $openid;
			$filter['type'] = 1;
			$list_sn = D('Weixin_relaylist')->where($filter)->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($list_sn){
				$this->assign('list_sn',$list_sn);
				//$this->pagedata['list_sn'] = $list_sn;
			}

			$filter['openid'] = $openid;
			$filter['type'] = 2;
			import('ORG.Util.Page'); // 导入分页类
			$count = $objsn->where($filter)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$list_sn2 = $objsn->where($filter)->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($list_sn2){
				$list_sn3 = $arr3 = array();
				foreach($list_sn2 as $k => $v){
					
					if(in_array($v['jieliid'],$arr3)){
						$list_sn3[$v['jieliid']]['gold'] += $v['gold'];
						$list_sn3[$v['jieliid']]['cishu'] += 1;
					}else{
						$arr3[] = $v['jieliid'];
						$list_sn3[$v['jieliid']] = $v;
						$list_sn3[$v['jieliid']]['cishu'] = 1;
					}
				}
				$this->assign('list_sn3',$list_sn3);
				//$this->pagedata['list_sn3'] = $list_sn3;
			}

			$detail ['follower_condtion'] == 1 && $condition [] = '必须微信关注后才能领取';
			$detail ['member_condtion'] == 1 && $condition [] = '必须是平台会员才能领取';
			$this->assign('condition',$condition);
			//$this->pagedata['condition'] = $condition;
			
			if($my_sn_list){
				$url = __HOST__.U('relay/show', array('sn_id'=>$my_sn_list['sn_id']));
				$this->assign('url',$url);
				//$this->pagedata['url'] = $this->mklink('weixin/relay/show', array($my_sn_list['sn_id']));
			}
			$weixin =D('User_weixin')->where(array('openid',$openid))->select();
			$member = D('User')->find($weixin['user_id']);
			//$member =  K::M('member/weixin')->detail_by_openid($openid);

			$stime = strtotime($detail ['stime']);
				$ltime = strtotime($detail ['ltime']);
				if (! empty ( $ltime ) && $ltime <= time ()) {
				$error = '您来晚啦';
			}else if ($detail ['follower_condtion'] == 1 && $wx_info['subscribe'] == 0) {
				switch ($detail ['follower_condtion']) {
					case 1 :
						$error = '关注后才能领取';
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				$error = '用户注册后才能领取';
			}
			$this->assign('details', D('Shopdetails')->itemsByIds($shop_ids));//二维码图片
			$this->assign('time',time());
			$this->assign('error',$error);
			//$this->pagedata['time'] = time();
			//$this->pagedata['error'] = $error;
			$this->display();
			//$this->tmpl = 'weixin/relay/prev.html';
		}
	}

	public function sign($relay_id,$qian,$sn_id=null)
	{
		$obj = D('Weixin_relay');
		$objsn = D('Weixin_relaysn');
		if(!($relay_id = (int)$relay_id) && !($relay_id = $this->_post('relay_id'))){
			$this->Error('未指定该助力ID');
			//$this->err->add('未指定该接力ID', 217);
        }else if(!$detail = $obj->find($help_id)){
			$this->Error('该助力不存在或已经删除');
			//$this->err->add('该接力不存在或已经删除', 216);
        }else{
			if($sn_id<=0){
				if(empty($openid)){
					$openid = $this->access_openid($detail['shop_id']);
				}
				$client = $this->wechat_client($detail['shop_id']);
				$wx_info = $client->getUserInfoById($openid);
		//$openid = '111111';
				$weixin =D('User_weixin')->where(array('openid',$openid))->select();
				$member = D('User')->find($weixin['user_id']);
				//$member =  K::M('member/weixin')->detail_by_openid($openid);
				$stime = strtotime($detail ['stime']);
				$ltime = strtotime($detail ['ltime']);
				
				if (! empty ( $ltime ) && $ltime <= time ()) {
					$this->err->add('您来晚啦', 215);
				}else if ($detail ['follower_condtion'] && $wx_info['subscribe'] == 0) {
					switch ($detail ['follower_condtion']) {
						case 1 :
							$this->err->add('关注后才能领取', 214);
							break;
					}
				}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
					$this->err->add('用户注册后才能领取', 213);
				}else{
					$data ['cishu'] = 1;
					$data ['uid'] = $member['uid'];
					$data['shop_id'] = $detail['shop_id'];
					$data['relay_id'] = $relay_id;
					$data['openid'] = $openid;
					$data['nickname'] = $wx_info['nickname'];
					$data['gold'] = $qian;
					$data['img'] = $wx_info['headimgurl'];//修改
					if($sn = $objsn->add($data)){
						$datas['openid'] = $openid;
						$datas['shop_id'] = $detail['shop_id'];
						$datas['relay_id'] = $detail['relay_id'];
						$datas['nickname'] = $wx_info['nickname'];
						$datas['img'] = $wx_info['headimgurl'];//修改
						$datas['type'] = 1;
						$datas['gold'] = $qian;
						if($list = D('Weixin_relaylist')->add($datas)){
							$this->baoreturn('0',$qian);
							//$this->Error($msg);
						}
					}
				}
			}else{
				if(empty($openid)){
					$openid = $this->access_openid($detail['shop_id']);
				}
				$client = $this->wechat_client($detail['shop_id']);
				$wx_info = $client->getUserInfoById($openid);
	//$openid = '22222222222';
				$list =$objsn->where(array('relay_id'=>$relay_id,'openid'=>$openid))->select();
				//$list = K::M('weixin/relaysn')->items(array('relay_id'=>$relay_id,'openid'=>$openid));

				foreach($list as $k => $v){
					if($v['cishu']>= $detail['max_num']){
						$this->baoreturn('0','您已经没有接力次数了');
						//$this->Error('您已经没有接力次数了');
						//$this->err->add('您已经没有接力次数了', 213);
					}else if($v['openid'] != $openid){
						$this->Error('用户错误');
						//$this->err->add('用户错误', 214);
					}else{
						$aa = $objsn->find($sn_id);
						$bb['cishu'] = $aa['cishu'] +1;
						$bb['sn_id'] = $aa['sn_id'];
						$objsn->save($bb);
						//K::M('weixin/relaysn')->update_count($sn_id, 'cishu', 1);
						$a = $objsn->find($sn_id);
						$b['gold'] = $a['gold'] + $qian;
						$b['sn_id'] = $a['sn_id'];
						$objsn->save($b);
						//K::M('weixin/relaysn')->update_count($sn_id, 'gold', $qian);
						$datas['openid'] = $openid;
						$datas['shop_id'] = $detail['shop_id'];
						$datas['relay_id'] = $detail['relay_id'];
						$datas['nickname'] = $wx_info['nickname'];
						$datas['img'] = $wx_info['headimgurl'];//修改
						$datas['type'] = 1;
						$datas['gold'] = $qian;
						if($sn1 = D('Weixin_relaylist')->add($datas)){
							$this->baoreturn('0',$qian);
							//$this->Error($qian);
							//$this->err->add($qian);
						}
					}
				}
			}
		}
	}

	public function fenxiang($sn_id)
	{
		$obj = D('Weixin_relay');
		$objsn = D('Weixin_relaysn');
		if(!($sn_id = (int)$sn_id) && !($sn_id = $this->_post('sn_id'))){
			$this->Error('该用户不存在');
            //$this->err->add('该用户不存在', 211);
        }else if(!$detail = $objsn->find($sn_id)){
             $this->Error('该用户不存在');
        }else{
			 $this->Error('分享成功');
		}
	}

	public function show($sn_id=null)
	{
		
		$obj = D('Weixin_relay');
		$objsn = D('Weixin_relaysn');
        if(!($sn_id = (int)$sn_id) && !($sn_id = $this->GP('sn_id'))){
            $this->Error('该用户不存在');
        }else if(!$helpsn = $objsn->find($sn_id)){
           $this->Error('该用户不存在');
        }else if(!$detail =$obj->find($helpsn['relay_id'])){
			$this->Error('该接力不存在或已经删除');
        }else{
			
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);
		//$openid = '99999999999999';	
			if($openid == $helpsn['openid']){
				$this->Error('不能自己给自己接力');
			}else{
				$relaysn = $helpsn;
				$relay_id = $relaysn['relay_id'];
				$list = $objsn->where(array('relay_id'=>$helpsn['relay_id'],'openid'=>$helpsn['openid']))->select();
				//$list = K::M('weixin/relaysn')->items(array('relay_id'=>$relay_id,'openid'=>$relaysn['openid']));
				foreach($list as $k => $v){
					$my_sn_list = $v;
					$this->assign('my_sn_list',$v);
					//$this->pagedata['my_sn_list'] = $my_sn_list = $v;
				}
				$aa = $obj->find($helpsn['relay_id']);
				$bb['views'] = $aa['views'] +1;
				$bb['relay_id'] = $aa['relay_id'];
				$obj->save($bb);
				//K::M('weixin/relay')->update_count($relay_id, 'views', 1);
                $jsSdk = $this->weixin_jssdk($weixin['app_id'], $weixin['app_key']);
			    $jsSdk1 = $jsSdk->getSignPackage();
				$this->assign('wxjscfg',$jsSdk1);
				//$this->pagedata['wxjscfg'] = $jsSdk->getSignPackage();

				//mine
				$my_count = D('Weixin_relaylist')->where(array('relay_id'=>$relay_id,'openid'=>$relaysn['openid'],'jieliid'=>$openid))->count();
				$my_list  = D('Weixin_relaylist')->where(array('relay_id'=>$relay_id,'openid'=>$relaysn['openid'],'jieliid'=>$openid))->select();
				//$my_list = K::M('weixin/relaylist')->items(array('relay_id'=>$relay_id,'openid'=>$relaysn['openid'],'jieliid'=>$openid),null,1,100,$my_count);
				
				foreach($my_list as $k => $v){
					$gold_all += $v['gold'];
				}
				$this->assign('my_count',$my_count);
				$this->assign('gold_all',$gold_all);
				$this->assign('detail',$detail);
				//$this->pagedata['my_count'] = $my_count;
				//$this->pagedata['gold_all'] = $gold_all;
				//$this->pagedata['detail'] = $detail;



				$filter['relay_id'] = $relay_id;
				$prizes = D('Weixin_relayprize')->where($filter)->select();
				if($prizes){
					$this->assign('prizes',$prizes);
					//$this->pagedata['prizes'] = $prizes;
				}
				$sn_list = $objsn->where($filter)->order( array('gold'=>'desc'))->select();
				if($sn_list){
					$this->assign('sn_list',$sn_list);
					//$this->pagedata['sn_list'] = $sn_list;
				}

				$filter['openid'] = $relaysn['openid'];
				$filter['type'] = 1;
				$list_sn = D('Weixin_relaylist')->where($filter)->limit($Page->firstRow . ',' . $Page->listRows)->select();
				if($list_sn){
					$this->assign('list_sn',$list_sn);
					//$this->pagedata['list_sn'] = $list_sn;
				}

				$filter['openid'] = $relaysn['openid'];
				$filter['type'] = 2;
				$filter['openid'] = $openid;
				$list_sn2 = D('Weixin_relaylist')->where($filter)->limit($Page->firstRow . ',' . $Page->listRows)->select();

				if($list_sn2){
					$list_sn3 = $arr3 = array();
					foreach($list_sn2 as $k => $v){
						
						if(in_array($v['jieliid'],$arr3)){
							$list_sn3[$v['jieliid']]['gold'] += $v['gold'];
							$list_sn3[$v['jieliid']]['cishu'] += 1;
						}else{
							$arr3[] = $v['jieliid'];
							$list_sn3[$v['jieliid']] = $v;
							$list_sn3[$v['jieliid']]['cishu'] = 1;
						}
					}
					$this->assign('list_sn3',$list_sn3);
					//$this->pagedata['list_sn3'] = $list_sn3;
				}

				$detail ['follower_condtion'] == 1 && $condition [] = '必须微信关注后才能领取';
				$detail ['member_condtion'] == 1 && $condition [] = '必须是平台会员才能领取';
				$this->assign('condition',$condition);
				//$this->pagedata['condition'] = $condition;
			
				if($my_sn_list){
					$url = U('relay/show', array('sn_id'=>$my_sn_list['sn_id']));
					$this->assign('url',$url);
					//$this->pagedata['url'] = $this->mklink('weixin/relay/show', array($my_sn_list['sn_id']));
				}
				$url2 = U('relay/preview', array('relay_id'=>$detail['relay_id']));
				$this->assign('url2',$url2);
				//$this->pagedata['url2'] = $this->mklink('weixin/relay/preview', array($detail['relay_id']));
				//$member =  K::M('member/weixin')->detail_by_openid($openid);
				$weixin =D('User_weixin')->where(array('openid',$openid))->select();
				$member = D('User')->find($weixin['user_id']);
				$stime = strtotime($detail ['stime']);
				$ltime = strtotime($detail ['ltime']);
				if (! empty ( $ltime ) && $ltime <= time ()) {
					$error = '您来晚啦';
				}else if ($detail ['follower_condtion'] == 1 && $wx_info['subscribe'] == 0) {
					switch ($detail ['follower_condtion']) {
						case 1 :
							$error = '关注后才能领取';
							break;
					}
				}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
					$error = '用户注册后才能领取';
				}	
				$this->assign('time',time());
				//$this->pagedata['time'] = time();
				$this->assign('error',$error);
				//$this->pagedata['error'] = $error;
				
				$this->display();
				//$this->tmpl = 'weixin/relay/show.html';
			}
		}
	}

	public function sign2($relay_id,$qian,$sn_id)
	{
		$obj = D('Weixin_relay');
		$objsn = D('Weixin_relaysn');
		if(!($relay_id = (int)$relay_id) && !($relay_id = $this->GP('relay_id'))){
			$this->baoreturn('0','未指定该接力ID');
        }else if(!$detail = $obj->find($help_id)){
			$this->baoreturn('0','该接力不存在或已经删除');
			//$this->err->add('该接力不存在或已经删除', 216);
        }else if(!$relaysn = $objsn->find($sn_id)){
			$this->baoreturn('0','该接力玩家不存在');
			//$this->err->add('该接力玩家不存在', 217);
		}else{
			if(empty($openid)){
				$openid = $this->access_openid(detail['shop_id']);
			}
			$client = $this->wechat_client(detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);
	//$openid = '99999999999999';
			$my_list = D('Weixin_relaylist')->where(array('relay_id'=>$relay_id,'openid'=>$relaysn['openid'],'jieliid'=>$openid))->select();
			
			//$my_list = K::M('weixin/relaylist')->items(array('relay_id'=>$relay_id,'openid'=>$relaysn['openid'],'jieliid'=>$openid),null,1,100,$my_count);

			if($my_count >= $detail['relay_num']){
				$this->baoreturn('0','您已经没有接力次数了');
	
				//$this->err->add('您已经没有接力次数了', 213);
			}else{
				$aa = $objsn->find($sn_id);
				$bb['gold'] = $aa['gold'] + $qian;
				$bb['sn_id'] = $aa['sn_id'];
				$objsn->save($bb);
				//K::M('weixin/relaysn')->update_count($sn_id, 'gold', $qian);
				$datas['openid'] = $relaysn['openid'];
				$datas['jieliid'] = $openid;
				$datas['shop_id'] = $detail['shop_id'];
				$datas['relay_id'] = $detail['relay_id'];
				$datas['nickname'] = $wx_info['nickname'];
				$datas['img'] = $wx_info['headimgurl'];//修改
				$datas['type'] = 2;
				$datas['gold'] = $qian;
				$datas['dateline'] = time();
				if($list = D('Weixin_relaylist')->add($datas)){
					//$this->Error($qian);
					$this->baoreturn('0',$qian);
				}
			}
		}
	}
}