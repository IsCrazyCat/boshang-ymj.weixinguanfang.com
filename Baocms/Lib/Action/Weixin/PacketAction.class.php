<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class PacketAction extends CommonAction {

	
	public function index($packet_id)
	{
		$obj = D('Weixin_packet');
		$objsn = D('Weixin_packet sn');
        if(!($packet_id = (int)$packet_id) && !($packet_id = $this->_post('packet_id'))){
        	$this->Error('没有指定红包ID');
        }else if(!$detail = $obj->find($packet_id)){
			$this->Error('该红包不存在或已经删除');
        }else if($detail['is_open'] == 0){
			$this->Error('活动还没有开启');
		}else if(!$shop_id = $detail['shop_id']){
			$this->Error('您没有获取到抽奖认可');
		}else{
            if(empty($openid)){
                $openid = $this->access_openid($detail['shop_id']);
            }
			$this->assign('details', D('Shopdetails')->itemsByIds($shop_ids));//二维码图片
			$this->assign('detail',$detail);
			$this->assign('is_start',$is_start);
			$this->display();
		}
    }
	
	function show($id,$shop_id) {

		if(empty($openid)){
			$openid = $this->access_openid($shop_id);
		}

//$openid = 111111;
		if(!($id = (int)$id) && !($id = $this->_post('id'))){
			$result['err'] 	= -1;
			$result['msg'] 	= '没有指定红包ID';
			echo json_encode($result);
			exit;
        }else if(!$detail =D('Weixin_packet')->find($id)){
			$result['err'] 	= -2;
			$result['msg'] 	= '该红包不存在或已经删除';
			echo json_encode($result);
			exit;
        }else if($detail['is_open'] == 0){
			$result['err'] 	= -3;
			$result['msg'] 	= '活动还没有开启';
			echo json_encode($result);
			exit;
		}else if($detail['shop_id'] != $shop_id){
			$result['err'] 	= -4;
			$result['msg'] 	= '您没有获取到抽奖认可';
			echo json_encode($result);
			exit;
		}else{

			
			$client = $this->wechat_client($shop_id);
			$wx_info = $client->getUserInfoById($openid);
	//$openid = '999999';
			$shop_id	=	$detail['shop_id'];
			if($this->is_start($id) == 1){
				$result['err'] 	= 1;
				$result['msg'] 	= '活动还没有开始，请耐心等待！';
				echo json_encode($result);
				exit;
			}else if($this->is_start($id) == 2){
				$result['err'] 	= 2;
				$result['msg'] 	= '活动已经结束，敬请关注下一轮活动开始！';
				echo json_encode($result);
				exit;
			}else{
				$items =D('Weixin_packetsn')->where(array('packet_id'=>$id,'open_id'=>$openid))->select();
				$p_count = count($items);
				
				/*奖品数量消耗完提示红包被领光*/
				if($p_count >= $detail['get_number']){
					$result['err'] 	= 3;
					$result['msg'] 	= '领取次数已经用光了！';
					echo json_encode($result);
					exit;
				}


				if(!$this->check_packet_type($id)){
						$result['err'] 	= 4;
						$result['msg'] 	= '红包已经领光啦，敬请关注下一轮活动开始！';
						echo json_encode($result);
						exit;
				}


				if($detail['packet_type'] == '1'){	
					$max 	= $detail['item_max'];//单个上限
					$min 	= $detail['item_min'];//单个下限
					if($this->packet_info['deci'] == 0){
						$prize 		= mt_rand($min,$max);
					}else if($detail['deci'] == 1){
						$prize 		= mt_rand($min*10,$max*10)/10;
					}else if($detail['deci'] == 2){
						$prize 		= sprintf("%.2f", mt_rand($min*100,$max*100)/100);
					}
							
					$prize_name = $prize.'元';
				
				}else{

					$unit 	= $detail['item_unit'];//面额
					$prize 		= $detail['item_unit'];
					$prize_name = $prize.'元';
				}

				$result['err'] 	= 0;
				$result['msg'] 	= '恭喜您抽中了<b class="pointcl">'.$prize_name.'</b>';
				$sn = array();
				$sn['shop_id'] 		= $shop_id;
				$sn['open_id'] 	    = $openid;
				$sn['packet_id'] 	= $id;
				$sn['prize_name'] 	= $prize_name;
				$sn['worth'] 		= $prize;
				$sn['add_time'] 	= time();
				$sn['type'] 		= $detail['packet_type'];
				$md5 				= $openid . $id . $prize . time();
				$sn['code'] 		= substr(md5($md5),0,12); 
				$sn_id 	= D('Weixin_packetsn')->add($sn);
				if($sn_id){
					echo json_encode($result);
					exit;	
				}else{
					$result['err'] 	= 5;
					$result['msg'] 	= '未知错误，请稍后再试';
					$result['type'] = $detail['packet_type'];
					$result['prize']= $prize;
					echo json_encode($result);
					exit;	
				}
			}
		}
	}

	

	public function is_start($id){
		$now = time();
		$is_start 	= 0;
		$detail = D('Weixin_packet')->find($id);
		$stime = strtotime($detail ['start_time']);
		$ltime = strtotime($detail ['end_time']);
		if($stime>$now){
			$is_start 	= 1;
		}else if($ltime<$now){
			$is_start	= 2;
		}else if(!$this->check_packet_type($id)){
			$is_start	= 3;
		}
		return $is_start;
	}

	public function check_packet_type($id){
		$flag 	= true;
		$detail = D('Weixin_packet')->find($id);
		$items = D('Weixin_packetsn')->where(array('packet_id'=>$id))->select();
		$pcount	= count($items);

		if($detail['people'] == 0 || $detail['people'] > $pcount){  //领取人数
			if($detail['packet_type'] == '1'){	
				$sum = $detail['item_sum'];//总额
				$lsum = 0;
				foreach($items as $k => $v){
					$lsum += $v['worth'];
				}
				
				if($sum <=$lsum){
					$flag 		= false;
				}
	
			}else if($detail['packet_type'] == '2'){
				$num 	= $detail['item_num'];//领取数量
				if($num <=$pcount){
					$flag 		= false;
				}
			}
		}else{
			$flag 		= false;
		}
		
		return $flag;
	}

	public function my_packet($id,$shop_id,$page = 1){
		
		$filter = $pager = array();
        $pager['page'] = max(intval($page), 1);
        $pager['limit'] = $limit = 10;

		if(empty($openid)){
			$openid = $this->access_openid();
		}
	//$openid ='999999';
		$filter	 = array('open_id'=>$openid,'packet_id'=>$id);
		$items =D('Weixin_packetsn')->where($filter)->count();
		$items =D('Weixin_packetsn')->where($filter)->select();
		//$prizes = $objp->where($filter)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		if ($items) {
			$this->assign('items',$items);
        }
		$this->display();
	}
		
	public function all_packet($id,$shop_id){
		
		$filter = $pager = array();
		
		if(empty($openid)){
			$openid = $this->access_openid();
		}
	//$openid ='999999';
		$filter	 	= array('packet_id'=>$id);
		$opens = array();
		$objp =  D('Weixin_packetsn');
		import('ORG.Util.Page'); // 导入分页类
		$count = $objp->where($filter)->count();
		$Page = new Page($count, 15);
		$show = $Page->show();
		$items = $objp->where($filter)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		if ($items) {
            $pager['count'] = $count;
			foreach($items as $k => $v){
				if($opens[$v['open_id']]){
					continue;
				}else{
					$user = D('User_weixin')->where(array('open_id'=>$v['open_id']))->select();
					$opens[$v['open_id']] =$user;
					//$opens[$v['open_id']] = K::M('member/weixin')->detail_by_openid($v['open_id']);
				}
			}
			$this->assign('weixin', $opens);
			//$this->pagedata['weixin'] = $opens;
            //$pager['pagebar'] = $this->mkpage($count, $limit, $page, $this->mklink('weixin/packet:index', array($id,$shop_id,'{page}')));
            $this->assign('items', $items);
			$this->assign('page', $show); // 赋值分页输出
        }
		$this->display();
	}

	public function is_packet($id,$shop_id,$page = 1){
		
		$filter = $pager = array();
		$objp =  D('Weixin_packetsn');
		import('ORG.Util.Page'); // 导入分页类
		$count = $objp->where($filter)->count();
		$Page = new Page($count, 15);
		$show = $Page->show();
		$items = $objp->where($filter)->order(array('sn_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //$pager['page'] = max(intval($page), 1);
        //$pager['limit'] = $limit = 10;

		if(empty($openid)){
			$openid = $this->access_openid();
		}
//$openid ='999999';
		$filter	 	= array('open_id'=>$openid,'packet_id'=>$id,'is_reward'=>'1');
		$objp =  D('Weixin_packetsn');
		import('ORG.Util.Page'); // 导入分页类
		$count = $objp->where($filter)->count();
		$Page = new Page($count, 15);
		$show = $Page->show();
		$items = $objp->where($filter)->order(array('sn_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
      
		if ($items ) {
			$this->assign('items',$items);
        }
		$this->display('my_packet');
		//$this->tmpl = 'weixin/packet/my_packet.html';
	}


	public function reward_forms($sn_id){
		if(empty($openid)){
			$openid = $this->access_openid();
		}
	//$openid ='111111';
		if(!($sn_id = (int)$sn_id) && !($sn_id = $this->_post('sn_id'))){
			$this->Error('没有指定获奖用户ID');
        }else if(!$detail = D('Weixin_packetsn')->find($sn_id)){
			$this->Error('该获奖用户不存在或已经删除');
        }else{
			$this->assign('detail',$detail);
			$this->display();
		}
	}
	
	public function reward_sub($sn_id,$packet_id,$pwd,$mobile){
		$data 		= array();
		$result 	= array();
		$ptype		= 1;
		$detail =D('Weixin_packet')->find($packet_id);
		//$detail = K::M('weixin/packet')->detail($packet_id);
		if(empty($openid)){
			$openid = $this->access_openid();
		}
	//$openid ='999999';
		$filter = array('shop_id'=>$detail['shop_id'],'open_id'=>$openid,'packet_id'=>$packet_id);	
		
		$filter['id']	= $sn_id;
		$d =D('Weixin_packetsn')->where($filter)->select();
		//$d= K::M('weixin/packetsn')->items($filter);
		foreach($d as $k => $v){
			$packetsn = $v;
			$price = $v['worth'];
		}

		
		
		if($packetsn['is_reward'] == 2){
			$result['err']	= 1;
			$result['info']	= '请不要重复兑换';
			echo json_encode($result);
			exit;
		}
	
		if($ptype == 1){
			if($detail['password'] != $pwd){
				$result['err']	= 2;
				$result['info']	= '兑换密码错误';
				echo json_encode($result);
				exit;
			}
		}

		$data['shop_id'] 		= $detail['shop_id'];
		$data['open_id'] 	= $openid;
		$data['price'] 		= $price;
		$data['packet_id']  = $packet_id;
		$data['status']  	= 1;
		$data['type']  		= $ptype;
		$data['time'] 		= time();
		$data['sn_id']		= $sn_id;
		$data['mobile']		= $mobile;
		if($ptype == 1){
			$data['type_name'] 		= '线下兑换';
		}else if($ptype == 2){
			$data['type_name'] 		= '转入会员卡';
		}else if($ptype == 3){
			$data['type_name'] 		= '手机充值';
			$data['mobile']  		= $this->GP['mobile'];
			$data['status']  		= 0;
		}
		if(D('Weixin_packetling')->add($data)){
			D('Weixin_packetsn')->find($sn_id);
			$data1['id'] = $sn_id;
			$data1['is_reward'] = '2';
            $data1['addtime'] =time();
        $aaa =  D('Weixin_packetsn')->find($sn_id);
			D('Weixin_packetsn')->save($data1);
			//K::M('weixin/packetsn')->update($sn_id,array('is_reward'=>2));
			$result['err']	= 0;
			$result['info']	= '兑奖成功！请等待';
			echo json_encode($result);
			exit;
		}else{
			$result['err'] 	= 5;
			$result['info'] = '未知错误，请稍后再试';
			echo json_encode($result);
			exit;	
		}
	}

	public function rule($id)
	{
		if(!($id = (int)$id) && !($id = $this->_post('id'))){
			$this->Error('没有指定红包ID');
        }else if(!$detail =D('Weixin_packet')->find($id)){
			$this->Error('该红包不存在或已经删除');
        }else{
			$this->assign('detail',$detail);
			$this->display();
		}	
	}
}