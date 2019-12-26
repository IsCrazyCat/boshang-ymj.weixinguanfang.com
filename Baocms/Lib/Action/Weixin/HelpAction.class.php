<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class HelpAction extends CommonAction {

	public function index()
    {
        exit();
    }
	
	public function preview($help_id=null)
	{
		$obj = D('Weixin_help');
		$objsn = D('Weixin_helpsn');
        if(!($help_id = (int)$help_id) && !($help_id = $this->_post('help_id'))){
			$this->assign('error','未指定内容ID');
			$this->display("public:404");
        }else if(!$detail = $obj->find($help_id)){
			header("HTTP/1.0 404 NOT FOUND");
			$this->assign('error','该助力不存在或已经删除');
			$this->display("public:404");
			//$this->Error('该助力不存在或已经删除');
        }elseif(!$weixin =D('Shopdetails')->where(array('shop_id'=>$detail['shop_id']))->find()){
			$this->Error('该商家还未绑定公众号');
		}else{
			$stime = strtotime($detail ['s_time']);
			$ltime = strtotime($detail ['l_time']);
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);
//$openid = 111111;

			$list =$objsn->where(array('help_id'=>$help_id,'openid'=>$openid))->select();
			//$list = K::M('weixin/helpsn')->items(array('help_id'=>$help_id,'openid'=>$openid));
			foreach($list as $k => $v){
				$my_sn_list = $v;
				$this->assign('my_sn_list',$v);
				//$this->pagedata['my_sn_list'] = $my_sn_list = $v;
			}
			$aa = $obj->find($help_id);
			$bb['views'] = $aa['views'] +1;
			$bb['help_id'] = $aa['help_id'];
			$obj->save($bb);

			$jsSdk = $this->weixin_jssdk($weixin['app_id'], $weixin['app_key']);
			$jsSdk1 = $jsSdk->getSignPackage();
			$this->assign('wxjscfg',$jsSdk1);
			//$this->pagedata['wxjscfg'] = $jsSdk->getSignPackage();
			$this->assign('detail',$detail);
			//$this->pagedata['detail'] = $detail;

			$filter['help_id'] = $help_id;
			$prizes = D('Weixin_helpprize')->where($filter)->select();
			if($prizes){
				$this->assign('prizes',$prizes);
				//$this->pagedata['prizes'] = $prizes;
			}
			import('ORG.Util.Page'); // 导入分页类 
			$count = $objsn->where($filter)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$sn_list = $objsn->where($filter)->order(array('zhuli' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($sn_list){
				//$this->pagedata['sn_list'] = $sn_list;
				$this->assign('sn_list',$sn_list);
			}
			$Page = array();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$filter['openid'] = $openid;
			$list_sn = D('Weixin_helplist')->where($filter)->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($list_sn){
				//$this->pagedata['list_sn'] = $list_sn;
				$this->assign('list_sn',$list_sn);
			}
			

			
			if($my_sn_list){
				$url = __HOST__.U('help/show', array('sn_id'=>$my_sn_list['sn_id']));
                
				$this->assign('url',$url);
				//$this->pagedata['url'] = $this->mklink('weixin/help/show', array($my_sn_list['sn_id']));
			}
			$weixin =D('User_weixin')->where(array('openid',$openid))->select();
			$member = D('User')->find($weixin['user_id']);
			//$member =  K::M('member/weixin')->detail_by_openid($openid);

			if (!empty ( $ltime ) && $ltime <= time ()) {
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
			$this->assign('error',$error);
			//$this->pagedata['error'] = $error;
			$this->display();
			//$this->tmpl = 'weixin/help/prev.html';
		}
	}

	public function sign($help_id=null)
	{
		$obj = D('Weixin_help');
		$objsn = D('Weixin_helpsn');
		if(!($help_id = (int)$help_id) && !($help_id = $this->_post('help_id'))){
			$this->Error('未指定该助力ID');
        }else if(!$detail = $obj->find($help_id)){
			$this->Error('该助力不存在或已经删除');
        }else{
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
//$openid = 111111;
			$stime = strtotime($detail ['stime']);
			$ltime = strtotime($detail ['ltime']);
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);
			$weixin =D('User_weixin')->where(array('openid',$openid))->select();
			$member = D('User')->find($weixin['user_id']);
			//$member =  K::M('member/weixin')->detail_by_openid($openid);
			$list =$objsn->where(array('help_id'=>$help_id,'openid'=>$openid))->select();
			//$list = K::M('weixin/helpsn')->items(array('help_id'=>$help_id,'openid'=>$openid));
			
			if (! empty ( $ltime ) && $ltime <= time ()) {
				$this->Error('您来晚啦');
			}else if ($detail ['follower_condtion'] && $wx_info['subscribe'] == 0) {
				switch ($detail ['follower_condtion']) {
					case 1 :
						$this->Error('关注后才能领取');
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				$this->Error('用户注册后才能领取');
			}else{
				$data ['zhuli'] = 1;
				$data ['user_id'] = $member['user_id'];
				$data['shop_id'] = $detail['shop_id'];
				$data['help_id'] = $help_id;
				$data['openid'] = $openid;
				$data['nickname'] = $wx_info['nickname'];
				$data['img'] = $wx_info['headimgurl'];//修改
				if($sn = $objsn->add($data)){
					$msg = $data['nickname'].'|'.$data['img'].'|'.$data['zhuli'];
					$this->Error($msg);
					//$this->err->add($msg);
				}else {
					$this->Error('您已经参加过了');
				}
			}
			
		}
	}

	public function fenxiang($sn_id)
	{
		$obj = D('Weixin_help');
		$objsn = D('Weixin_helpsn');
		if(!($sn_id = (int)$sn_id) && !($sn_id = $this->_post('sn_id'))){
			$this->Error('该用户不存在');
        }else if(!$detail = $objsn->find($sn_id)){
            $this->Error('该用户不存在');
        }else{
			$data['zhuanfa'] = $detail['zhuanfa'] +1;
			$data['sn_id'] = $detail['sn_id'];
			$objsn->save($data);
			//K::M('weixin/helpsn')->update_count($sn_id, 'zhuanfa', 1);
			$this->Error('分享成功');
			//$this->err->add('分享成功');
		}
	}

	public function zhuli($sn_id)
	{
		$obj = D('Weixin_help');
		$objsn = D('Weixin_helpsn');
		if(!($sn_id = (int)$sn_id) && !($sn_id = $this->_post('sn_id'))){
			$this->Error('该用户不存在');
        }else if(!$detail = $objsn->find($sn_id)){
            $this->Error('该用户不存在');
        }else{
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);

	//$openid =111111;		
			$data['openid'] = $detail['openid'];
			$data['shop_id'] = $detail['shop_id'];
			$data['help_id'] = $detail['help_id'];

			$data['zhuliid'] = $openid;
			$data['nickname'] = $wx_info['nickname'];
			$data['img'] = $wx_info['headimgurl'];//修改
            $data['dateline'] = time();

			if($list = D('Weixin_helplist')->add($data)){
				$zhuli = $detail['zhuli'] +1;
				$objsn->save(array('sn_id'=>$sn_id,'zhuli'=>$zhuli));
				//K::M('weixin/helpsn')->update_count($sn_id, 'zhuli', 1);
				$this->Error('助力成功');
				//$this->err->add('助力成功');
			}else {
				$this->Error('助力失败');
				//$this->err->add('助力失败', 212);
			}
		}
	}

	public function show($sn_id)
	{
		$obj = D('Weixin_help');
		$objsn = D('Weixin_helpsn');
		if(!($sn_id = (int)$sn_id) && !($sn_id = $this->_post('sn_id'))){
			$this->Error('该用户不存在');
        }else if(!$helpsn = $objsn->find($sn_id)){
			$this->Error('该用户不存在');
        }else if(!$detail =$obj->find($helpsn['help_id'])){
			header("HTTP/1.0 404 该助力不存在11或已经删除");
			$this->display("public:404"); 
			//$this->Error('该助力不存在11或已经删除');
        }else{
            if(!$weixin =D('Shopdetails')->where(array('shop_id'=>$detail['shop_id']))->find()){
			$this->Error('该商家还未绑定公众号');
		    }

			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
				
			}
//$openid = 111211;
			if($openid == $helpsn['openid']){
				$this->Error('不能自己给自己助力');
			}else{
				$client = $this->wechat_client($detail['shop_id']);
				$wx_info = $client->getUserInfoById($openid);
				$list = $objsn->where(array('help_id'=>$helpsn['help_id'],'openid'=>$helpsn['openid']))->select();
				//$list = K::M('weixin/helpsn')->items(array('help_id'=>$helpsn['help_id'],'openid'=>$helpsn['openid']));
				foreach($list as $k => $v){
					$my_sn_list = $v;
					$this->assign('my_sn_list',$v);
					//$this->pagedata['my_sn_list'] = $my_sn_list = $v;
				}

				$filter['help_id'] = $helpsn['help_id'];
				$prizes = D('Weixin_helpprize')->where($filter)->select();
				if($prizes){
					$this->assign('prizes',$prizes);
					//$this->pagedata['prizes'] = $prizes;
				}
				import('ORG.Util.Page'); // 导入分页类 
				$count = $objsn->where($filter)->count();
				$Page = new Page($count, 15);
				$show = $Page->show();
				$sn_list = $objsn->where($filter)->order(array('zhuli' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
			
				if($sn_list){
					
					//$this->pagedata['sn_list'] = $sn_list;
					$this->assign('sn_list',$sn_list);
				}

				$filter['openid'] = $helpsn['openid'];
				$list_sn = D('Weixin_helplist')->where($filter)->limit($Page->firstRow . ',' . $Page->listRows)->select();
				if($list_sn){
					$this->assign('list_sn',$list_sn);
					//$this->pagedata['list_sn'] = $list_sn;
				}
				$aa = $obj->find($helpsn['help_id']);
				$bb['views'] = $aa['views'] +1;
				$bb['help_id'] = $aa['help_id'];
				$obj->save($bb);

				include "Baocms/Lib/Action/weixin/jssdk.php";
				//Import::L('weixin/jssdk.php');
                $jsSdk = $this->weixin_jssdk($weixin['app_id'], $weixin['app_key']);
				$jsSdk1 = $jsSdk->getSignPackage();
				$this->assign('wxjscfg',$jsSdk1);
				//$this->pagedata['wxjscfg'] = $jsSdk->getSignPackage();

				$condition = array ();

				$detail ['follower_condtion'] == 1 && $condition [] = '必须微信关注后才能领取';
				$detail ['member_condtion'] == 1 && $condition [] = '必须是平台会员才能领取';

				//$this->pagedata['condition'] = $condition;
				$this->assign('condition',$condition);
				if($my_sn_list){
					$url = U('help/show', array('sn_id'=>$my_sn_list['sn_id']));
					$this->assign('url',$url);
					//$this->pagedata['url'] = $this->mklink('weixin/help/show', array($my_sn_list['sn_id']));
				}
				$url12 = U('help/preview', array('help_id'=>$helpsn['help_id']));
				$this->assign('url',$url);
				//$this->pagedata['url2'] = $this->mklink('weixin/help/preview', array($helpsn['help_id']));

				//$member =  K::M('member/weixin')->detail_by_openid($openid);
				$weixin =D('User_weixin')->where(array('openid',$openid))->select();
				$member = D('User')->find($weixin['user_id']);
				$stime = strtotime($detail ['s_time']);
				$ltime = strtotime($detail ['l_time']);
				if (!empty ( $detail ['ltime'] ) && $detail ['ltime'] <= time ()) {
					$error = '您来晚啦';
				}else if ($detail ['follower_condtion'] == 1 && $wx_info['subscribe'] == 0) {
					switch ($detail ['follower_condtion']) {
						case 1 :
							$this->Error('关注后才能领取');
							break;
					}
				}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
					$this->Error('用户注册后才能领取');
				}
				$this->assign('error',$error);
				//$this->pagedata['error'] = $error;
				$this->assign('helpsn',$helpsn);
				//$this->pagedata['helpsn'] = $helpsn;
				$this->assign('detail',$detail);
				//$this->pagedata['detail'] = $detail;
				$list_sn = D('Weixin_helplist')->where(array('openid'=>$helpsn['openid'],'zhuliid'=>$openid))->select();
				if($list_sn){
					$this->assign('iszhuli',1);
					//$this->pagedata['iszhuli'] = '1';
				}
				$this->display();
				//$this->tmpl = 'weixin/help/show.html';
			}
		}
	}
}