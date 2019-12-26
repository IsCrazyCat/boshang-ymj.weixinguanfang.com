<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class CouponAction extends CommonAction 
{

	public function detail($coupon_id=null)
    {
		$obj = D('Weixin_coupon');
		if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->_post('coupon_id'))){
			$this->Error('未指定要修改的内容ID');
        }else if(!$detail = $obj->where(array('coupon_id'=>$coupon_id))->select()){
			$this->Error('该优惠券不存在或已经删除');
        }else{
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);
			 
	//$openid = '999999';	
			$objsn = D('Weixin_couponsn');
			$list = $obj->where(array('coupon_id'=>$coupon_id,'openid'=>$openid))->select();
			$data['views'] = $detail['views'] + 1;
			$data['coupon_id'] = $detail['coupon_id'];
			$obj->save($data);
			$this->assign('my_sn_list',$list);
			$this->assign('detail',$detail);
			
			$condition = array ();
			$detail ['max_count'] > 0 && $condition [] = '每人最多可领取' . $detail ['max_count'] . '张';
			$detail ['follower_condtion'] == 1 && $condition [] = '必须微信关注后才能领取';
			$detail ['member_condtion'] == 1 && $condition [] = '必须是平台会员才能领取';
			$this->assign('condition',$condition);
			$objm =  D('User_weixin');
			$obju =  D('User');
			$map = array();
			$map['openid'] = $openid;
			if($uid = $objm->where($map)->getField('user_id')){
				$member = $obju->find($uid);
			}else{
				$this->Error('微信参数错误');
			}
			$stime = strtotime($detail ['stime']);
			$ltime = strtotime($detail ['ltime']);
			if (!empty ( $ltime ) && $ltime <= time ()) {
				$error = '您来晚啦';
			} else if ($detail ['num']<=$detail['down_count']) {
				$error = '优惠券已经领取光啦';
			}else if ($detail ['max_count'] > 0 && $detail ['max_count'] <= count($list)) {
				$error = '您的领取名额已用完啦';
			} else if ($detail ['follower_condtion'] == 1 && $wx_info['subscribe'] == 0) {
				switch ($detail ['follower_condtion']) {
					case 1 :
						$error = '关注后才能领取';
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				$error = '用户注册后才能领取';
			}
			$this->assign('error',$error);
			$this->display();
		}
    }

	public function preview($shop_id)
	{
		$obj = D('Weixin_coupon');
		$objsn = D('Weixin_couponsn');
        if(!$shop_id){
			$this->Error('未指定内容ID');
        }else if(!$items = $obj->where(array('shop_id'=>$shop_id))->select()){
			$this->Error('该商家优惠券不存在');
        }else{
			$this->assign['items'] = $items;
			$this->assign['time'] = time();
			$stime = strtotime($items['stime']);
			$ltime = strtotime($items['ltime']);
			$this->assign['ltime'] = $ltime;
			$this->assign['stime'] = $stime;
			if(empty($openid)){
				$openid = $this->access_openid();
			}
	//$openid = '999999';
			$map =array();
			$map['shop_id'] = $shop_id;$map['openid'] = $openid;$map['is_use'] = 1;
			$list1 = $objsn->where($map)->select();
			$map =array();
			$map['shop_id'] = $shop_id;$map['openid'] = $openid;$map['is_use'] = 0;
			$list2 = $objsn->where($map)->select();
			$this->assign['list1'] = $list1;
			$this->assign['list2'] = $list2;
			$this->display();
		}
	}

	public function sign($coupon_id=null)
	{
		$obj = D('Weixin_coupon');
		$objsn = D('Weixin_couponsn');
		if(!($coupon_id = (int)$coupon_id) && !($coupon_id = $this->_post('coupon_id'))){
			$this->Error('未指定要修改的内容ID');
        }else if(!$detail = $obj->find($coupon_id)){
			$this->Error('该优惠券不存在或已经删除');
        }else{
			
			if(empty($openid)){
				$openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);
			
			//$member =  K::M('member/weixin')->detail_by_openid($openid);
			
			//$openid = '999999';
			//$wx_info['nickname'] = 'xxxxx';

			$list = $objsn->where(array('coupon_id'=>$coupon_id,'openid'=>$openid))->select();
			$stime = strtotime($detail ['stime']);
			$ltime = strtotime($detail ['ltime']);
			if (! empty ( $ltime ) && $ltime <= time ()) {
				$error = '您来晚啦';
			} else if ($detail ['max_count'] > 0 && $detail ['max_count'] <= count($list)) {
				$error = '您的领取名额已用完啦';
			} else if ($detail ['num']<=$detail['down_count']) {
				$error = '优惠券已经领取光啦';
			}else if ($detail ['follower_condtion'] && $wx_info['subscribe'] == 0) {
				switch ($detail ['follower_condtion']) {
					case 1 :
						$error = '关注后才能领取';
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				$error = '用户注册后才能领取';
			}else{
				$data ['sn'] = uniqid ();
				$data ['uid'] = $member['uid'];
				$data['shop_id'] = $detail['shop_id'];
				$data['coupon_id'] = $coupon_id;
				$data['openid'] = $openid;
				$data['nickname'] = $wx_info['nickname'];
				$data['dateline'] = time();
				if($sn = $objsn->add($data)){
					$data1['down_count'] = $detail['down_count'] + 1;
					$data1['coupon_id'] = $coupon_id;
					$obj->save($data1);
					//K::M('weixin/coupon')->update_count($coupon_id, 'down_count', 1);
					
					$qrurl =  U('coupon/show', array('sn' => $sn));
					 header("Location:{$qrurl}");
				}else {
					$error = '领取会员卡后才能领取';
				}
			}
			if($error){
				$this->assign('error',$error);
				//$this->assign['error'] = $error;
				$this->display('over');
				//$this->tmpl = 'weixin/coupon/over.html';
			}
		}
	}

	public function show($sn)
	{	
		if(empty($openid)){				
				$openid = $this->access_openid();			
				}
		$obj = D('Weixin_coupon');
		$objsn = D('Weixin_couponsn');
		if(!($sn = (int)$sn) && !($sn = $this->_post('sn'))){
			$this->Error('非法1访问');
        }else if(!$detail = $objsn->find($sn)){
            $this->Error('非法2访问');
        }else if(!$coupon = $obj->where(array('coupon_id'=>$detail['coupon_id']))->select()){
            $this->Error('非法3访问');
        }else if($openid != $detail['openid']){			
		$this->redirect("Coupon/detail",array('coupon_id'=>$detail['coupon_id']));		
		}else{
        	$this->assign('detail',$detail);
			//$this->assign['detail'] = $detail;
			$condition = array ();
			$coupon ['max_count'] > 0 && $condition [] = '每人最多可领取' . $coupon ['max_count'] . '张';
			$coupon ['follower_condtion'] == 1 && $condition [] = '必须微信关注后才能领取';
			$coupon ['member_condtion'] == 1 && $condition [] = '必须是平台会员才能领取';
			
			if($coupon){
				foreach($coupon as $k => $v){
					$coupon = $v;
				}
			}
			$this->assign('details', D('Shopdetails')->itemsByIds($shop_ids));//二维码图片
			$this->assign('coupon',$coupon);
			$this->assign('condition',$condition);
			$this->display();
		}
	}
}