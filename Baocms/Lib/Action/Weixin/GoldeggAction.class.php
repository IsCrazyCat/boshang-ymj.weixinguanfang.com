<?php



class GoldeggAction extends CommonAction {

   
	public function show($goldegg_id=0) {
		$id = (int)$goldegg_id;
		$obj = D('Weixin_goldegg');
		$objsn = D('Weixin_goldeggsn');
		if(!$id){
			$this->Error('没有指定砸金蛋ID');
        }else if(!$detail = $obj->find($id)){
			$this->Error('该砸金蛋不存在或已经删除');
        }else{
			if(empty($openid)){
				 $openid = $this->access_openid($detail['shop_id']);
			}
			$client = $this->wechat_client($detail['shop_id']);
			$wx_info = $client->getUserInfoById($openid);

	//$openid = 111111;
			$objm =  D('User_weixin');
			$obju =  D('User');
			$map = array();
			$map['openid'] = $openid;
			
			if($uid = $objm->where($map)->getField('user_id')){
				$member = $obju->find($uid);
			}else{				$this->redirect("Goldegg/show",array('goldegg_id'=>$goldegg_id));
				$this->Error('微信参数错误');
			}
			
			//$openid ='gh_d2eed246006e';
			//$member =  K::M('member/weixin')->detail_by_openid($openid);
			
			$filter['id'] = $id;
			$filter['openid'] = $openid;
			$now = time();
			$year=date('Y',$now);
			$month=date('m',$now);
			$day=date('d',$now);
			$firstSecond=mktime(0,0,0,$month,$day,$year);
			$lastSecond=mktime(23,59,59,$month,$day,$year);
			$filter['dateline']=array('between',array($firstSecond,$lastSecond));
			//$filter['dateline']= $firstSecond.'~'.$lastSecond;
			if($record =$objsn->where($filter)->select() ){
				$this->assign('record',$record);
			}
			$data=$detail;
			// 1. 中过奖金	
			if ($record['isegg'] == 1) {				
				$data['end'] = 5;
				$data['sn']	 	 = $record['sn'];
				$data['uname']	 = $record['shop_id'];
				$data['prize']	 = $record['prize'];
				$data['openid'] 	 = $record['openid'];	
			}
			$stime = strtotime($detail ['stime']);
			$ltime = strtotime($detail ['ltime']);
			if (!empty ( $detail ['stime'] ) && $stime >= time ()) {
				$this->Error('该活动还没开始');
			}else if (!empty ( $detail ['ltime'] ) && $ltime <= time ()) {
				$this->Error('您来晚啦');
			}else if ($detail ['max_count'] > 0 && $detail ['max_count'] <= count($list)) {
				$this->Error('您的领取名额已用完啦');
			}else if ($detail ['follower_condtion'] == 1 && $wx_info['subscribe'] == 0) {
				switch ($detail ['follower_condtion']) {
					case 1 :
						$this->Error('关注后才能领取');
						break;
				}
			}else if ($detail ['member_condtion'] == 1 && !$member['uname']) {
				$this->Error('用户注册后才能领取');
			}
/**/
			if(!$sdetail = $obj->find($id)){
				$this->Error('该砸金蛋不存在或已经删除');
			}else{
				$this->assign('sdetail',$sdetail);
			}
			$limit = 20;
			$orderby = array('sn_id'=>'DESC');
			$filter = array();
			//$filter['egg_id'] = $id;
			//$filter['prize'] = '<:7';
			$filter['prize'] = array('lt',7);
			$filter['egg_id'] = $id;
			//$filter['openid'] = $openid;
			$zitems = $objsn->where($filter)->limit($limit)->select();
			if($zitems){
				foreach($zitems as $k => $v){
					if($v['prize']==1){
						$zitems[$k]['prizes'] = "一等奖";
						$zitems[$k]['prizename'] = $sdetail['fist'];
					}else if($v['prize']==2){
						$zitems[$k]['prizes'] = "二等奖";
						$zitems[$k]['prizename'] = $sdetail['second'];
					}else if($v['prize']==3){
						$zitems[$k]['prizes'] = "三等奖";
						$zitems[$k]['prizename'] = $sdetail['third'];
					}else if($v['prize']==4){
						$zitems[$k]['prizes'] = "四等奖";
						$zitems[$k]['prizename'] = $sdetail['four'];
					}else if($v['prize']==5){
						$zitems[$k]['prizes'] = "五等奖";
						$zitems[$k]['prizename'] = $sdetail['five'];
					}else if($v['prize']==6){
						$zitems[$k]['prizes'] = "六等奖";
						$zitems[$k]['prizename'] = $sdetail['six'];
					}
				}
				$this->assign('zitems',$zitems);
			}
			$filter['openid'] = $openid;
			$myzitems = $objsn->where($filter)->limit($limit)->select();
			if($myzitems){
				foreach($myzitems as $k => $v){
					if($v['prize']==1){
						$myzitems[$k]['prizes'] = "一等奖";
						$myzitems[$k]['prizename'] = $sdetail['fist'];
					}else if($v['prize']==2){
						$myzitems[$k]['prizes'] = "二等奖";
						$myzitems[$k]['prizename'] = $sdetail['second'];
					}else if($v['prize']==3){
						$myzitems[$k]['prizes'] = "三等奖";
						$myzitems[$k]['prizename'] = $sdetail['third'];
					}else if($v['prize']==4){
						$myzitems[$k]['prizes'] = "四等奖";
						$myzitems[$k]['prizename'] = $sdetail['four'];
					}else if($v['prize']==5){
						$myzitems[$k]['prizes'] = "五等奖";
						$myzitems[$k]['prizename'] = $sdetail['five'];
					}else if($v['prize']==6){
						$myzitems[$k]['prizes'] = "六等奖";
						$myzitems[$k]['prizename'] = $sdetail['six'];
					}
				}
			
				$this->assign('myzitems',$myzitems);
			}
			//print_r($detail);exit;
			//$this->pagedata['error'] = $error;
			$data['On'] 		= 1;
			$data['shop_id']	    = $detail['shop_id'];		
			$data['lid']		= $detail['id'];
			$data['rid']		= intval($record['sn_id']);
			$data['usenums'] 	= $count;
			$data['info']=str_replace('&lt;br&gt;','<br>',$data['info']);
			$data['endinfo']=str_replace('&lt;br&gt;','<br>',$data['end_tips']);
			$record['id']=intval($record['sn_id']);
			$this->assign('Dazpan',$data);
			$this->assign('record',$record);			$this->assign('details', D('Shopdetails')->itemsByIds($shop_ids));//二维码图片
			$this->display();
		}
		
	}

	public function getajax()
	{	
		$shop_id	=	$this->_post('shop_id',false);
		$id 		=	$this->_post('id',false);
		$rid 		= 	$this->_post('rid',false);
		$objm =  D('User_weixin');
		$obju =  D('User');
		$obj = D('Weixin_goldegg');
		$objsn = D('Weixin_goldeggsn');
		if(empty($openid)){
			$openid = $this->access_openid($shop_id);
		}
		$client = $this->wechat_client($shop_id);
		$wx_info = $client->getUserInfoById($openid);

		//$member =  K::M('member/weixin')->detail_by_openid($openid);
	//$openid = 111111;
		$objm =  D('User_weixin');
		$obju =  D('User');
		$obj = D('Weixin_goldegg');
		$objsn = D('Weixin_goldeggsn');
		$map = array();
		$map['openid'] = $openid;
			
		if($uid = $objm->where($map)->getField('user_id')){
			$member = $obju->find($uid);
		}else{
			echo '{"error":1,"msg":"微信参数错误"}';exit;
		}
		$uid = $member['uid'];
		//$Lottery= K::M('weixin/goldegg')->detail($id);
		$Lottery= $obj->find($id);
		$stime = strtotime($Lottery['stime']);
		$ltime = strtotime($Lottery['ltime']);
		if ($stime>time()){
			echo '{"error":1,"msg":"活动还没开始，感谢关注"}';
			exit;
		}
		if ($ltime<time()){
			echo '{"error":1,"msg":"活动已经结束，感谢关注"}';
			exit;
		}

		if ($Lottery ['follower_condtion'] == 1 && $wx_info['subscribe'] == 0) {
			echo '{"error":1,"msg":"关注后才能领取"}';
			exit;
		}
		if ($detail ['follower_condtion'] == 1 && !$member['uname']) {
			echo '{"error":1,"msg":"用户注册后才能领取"}';
			exit;
		}
		$data=$this->prizeHandle($uid,$shop_id,$Lottery);
	//print_r($data);die;
		if ($data['end']==3){
			$sn	 	 = $data['sn'];
			$prize	 = $data['prize'];
			$tel 	 = $data['phone'];
			$msg = "您来晚了，奖品已被抽完了";
			echo '{"error":1,"msg":"'.$msg.'"}';
			exit;
		}
		if ($data['end']==-1){
			$msg = $data['winprize'];
			echo '{"error":1,"msg":"'.$msg.'"}';
			exit;
		}
		if ($data['end']==-2){
			$msg = $data['winprize'];
			echo '{"error":1,"msg":"'.$msg.'"}';
			exit;
		}
		if ($data['prizetype'] >= 1 && $data['prizetype'] <= 6) {
			echo '{"success":1,"sn":"'.$data['sncode'].'","prizetype":"'.$data['prizetype'].'","usenums":"'.$data['usenums'].'"}';
		}else{
			echo '{"success":0,"prizetype":"","usenums":"'.$data['prizetype'].'"}';
		}
		exit();
	}

	public function getPrizeName($Lottery,$prize)
	{
		$array = array('1'=>'frist','2'=>'second','3'=>'third','4'=>'four','5'=>'five','6'=>'six');
		return $Lottery[$array[$prize]];
	}

	public function prizeHandle($uid,$shop_id,$Lottery)
	{	
		if(empty($openid)){
			$openid = $this->access_openid($shop_id);
		}
		$client = $this->wechat_client($shop_id);
		$wx_info = $client->getUserInfoById($openid);

		//$member =  K::M('member/weixin')->detail_by_openid($openid);
	//$openid =111111;
		$objm =  D('User_weixin');
		$obju =  D('User');
		$obj = D('Weixin_goldegg');
		$objsn = D('Weixin_goldeggsn');
		$map = array();
		$map['openid'] = $openid;
		$member = $obju->find($uid);	
		/*if($uid = $objm->where($map)->getField('user_id')){
			$member = $obju->find($uid);
		}else{
			$this->Error('微信参数错误');
		}*/
		

		$now=time();
		$id = $Lottery['id'];
		$isday = 0;
		$where['openid'] = $openid;
		$where['egg_id'] = $id;
		$count = $objsn->where($where)->count();
		$stime = strtotime($Lottery['stime']);
		$ltime = strtotime($Lottery['ltime']);
		if($record =$objsn->where($where)->select()){
			$record['usenums'] = $count;
		}else{
			$record['usenums'] = 0;
		}
		
			if ($ltime < $now) { //过期
				$data['end'] = 2;
				$data['end_tips'] = $Lottery['end_tips'];
				$data['end_photo']  = empty($Lottery['end_photo']) ? 1 : $Lottery['end_photo'];
			}else{
				//最多抽奖次数
				$LotteryedRecordWhere=array('shop_id'=>$shop_id,'egg_id'=>$id,'isegg'=>1);
				$prizedCount = $objsn->where($LotteryedRecordWhere)->count();
				$aa = $objsn->where($LotteryedRecordWhere)->order(array('sn_id' => 'desc'))->select();
				//K::M('weixin/goldeggsn')->items($LotteryedRecordWhere,null,null,null,$prizedCount);
				if ($prizedCount >$Lottery['max_num']){
					$data['end'] = 3;
					$data['msg'] = "您已经中过奖了，不能再领取了，谢谢";
					$data['wxname']=$record['shop_id'];
					$data['wecha_name']=$record['nickname'];
					$data['sn']  = $record['sn'];
					$data['myprize'] 	= $this->getPrizeName($Lottery,$record['prize']);
					$data['prize'] 	= $record['prize'];
				}else {
					//是否已经够次数
					if ($record['usenums'] >= $Lottery['max_num'] ) {
						$data['end'] = -1;
						$data['prizetype'] = 4; //啥都没了
						$data['zjl']	  = 0;
						$data['usenums']  = $record['usenums'];
						$data['winprize']	   = "抽奖次数已经用完";
						//exit;
					}else{
						//当天的次数
						$year=date('Y',$now);
						$month=date('m',$now);
						$day=date('d',$now);
						$firstSecond=mktime(0,0,0,$month,$day,$year);
						$lastSecond=mktime(23,59,59,$month,$day,$year);
						foreach($record as $k => $v){
							if($v['dateline']>=$firstSecond && $v['dateline']<=$lastSecond){
								$thisDayNums++;
							}
						}
						if ($thisDayNums>=$Lottery['daynums']){
							$data['end'] = -2; //
							$data['zjl']	  = 0;
							$data['winprize']	   = "今天已经抽了".$thisDayNums."次了，没名额了，明天再来吧";
						}else {
							//3.没有领过,次数没达到,开始随机发放优惠券
							if($Lottery['fistlucknums']  == $Lottery['fistnums'] &&
							$Lottery['secondlucknums'] == $Lottery['secondnums'] &&
							$Lottery['thirdlucknums']  == $Lottery['thirdnums'] &&
							$Lottery['fourlucknums']  == $Lottery['fournums'] &&
							$Lottery['fivelucknums']  == $Lottery['fivenums'] &&
							$Lottery['sixlucknums']  == $Lottery['sixnums']
							){
								$prizeType=7;
							}else{
								$prizeType=intval($this->get_prize($Lottery));
							}
							//排除没有设置的优惠券
							//奖品数 != 已经领取该奖品数 => 还有奖品

							switch ($prizeType){
								default:
									$data['prizetype'] = 7; //啥都没了
									$data['zjl']	  = 0;
									$data['winprize']	   = "谢谢参与";
									$isegg=0;
									$data['sncode']    = '';
									break;
								case 1:
									$data['prizetype'] = 1;
									$data['sncode'] = uniqid();
									$data['winprize']	   = $Lottery['fist'];
									$data['zjl']	   = 1;
									
									//K::M('weixin/goldegg')->update_count($id, 'fistlucknums', 1);
									$aa['id'] = $id;
									$fistlucknums=$obj->where($aa)->getField('fistlucknums');
									$data['id'] = $id;
									$data['fistlucknums'] = $fistlucknums + 1 ;
									$obj->save($data);
									$isegg=1;
									break;
								case 2:
									$data['prizetype'] = 2;
									$data['winprize']  = $Lottery['second'];
									$data['zjl']	   = 1;
									$data['sncode']    = uniqid();
									$aa['id'] = $id;
									$secondlucknums=$obj->where($aa)->getField('secondlucknums');
									$data['id'] = $id;
									$data['secondlucknums'] = $secondlucknums + 1 ;
									$obj->save($data);
									$isegg=1;
									break;
								case 3:
									$data['prizetype'] = 3;
									$data['winprize']	   = $Lottery['third'];
									$data['zjl']	   = 1;
									$data['sncode'] = uniqid();
									$aa['id'] = $id;
									$thirdlucknums=$obj->where($aa)->getField('thirdlucknums');
									$data['id'] = $id;
									$data['thirdlucknums'] = $thirdlucknums + 1 ;
									$obj->save($data);
									$isegg=1;
									break;
								case 4:
									$data['prizetype'] = 4;
									$data['winprize']	   = $Lottery['four'];
									$data['zjl']	   = 1;
									$data['sncode'] = uniqid();
									$aa['id'] = $id;
									$fourlucknums=$obj->where($aa)->getField('fourlucknums');
									$data['id'] = $id;
									$data['fourlucknums'] = $fourlucknums + 1 ;
									$obj->save($data);
									//K::M('weixin/goldegg')->update_count($id, 'fourlucknums', 1);
									$isegg=1;
									break;
								case 5:
									$data['prizetype'] = 5;
									$data['winprize']	   = $Lottery['five'];
									$data['zjl']	   = 1;
									$data['sncode'] = uniqid();
									$aa['id'] = $id;
									$fivelucknums=$obj->where($aa)->getField('fivelucknums');
									$data['id'] = $id;
									$data['fivelucknums'] = $fivelucknums + 1 ;
									$obj->save($data);
									//K::M('weixin/goldegg')->update_count($id, 'fivelucknums', 1);
									$isegg=1;
									break;
								case 6:
									$data['prizetype'] = 6;
									$data['winprize']	   = $Lottery['six'];
									$data['zjl']	   = 1;
									$data['sncode'] = uniqid();
									$aa['id'] = $id;
									$sixlucknums=$obj->where($aa)->getField('sixlucknums');
									$data['id'] = $id;
									$data['sixlucknums'] = $sixlucknums + 1 ;
									$obj->save($data);
									$isegg=1;
									break;
							}
							//
						
							$data_sn = array('uid'=>$uid,'openid'=>$openid,'shop_id'=>$shop_id,'egg_id'=>$Lottery['id'],'nickname'=>$wx_info['nickname'],'isegg'=>$isegg,'dateline'=>time(),'sn'=>$data['sncode'],'prize'=>intval($data['prizetype']));
							$sn_id =$objsn->add($data_sn);

							//$sn_id = K::M('weixin/goldeggsn')->create($data_sn);
							
							//
							//$this->lottery_record_db->where(array('lid'=>$id,'wecha_id'=>$wecha_id))->setInc('usenums');
							//$record['usenums']=intval($record['usenums'])+1;
						}

					}//以上没领过
				}
			}
		//}
		//
		$record =$objsn->find($sn_id);
		//$record 	=  K::M('weixin/goldeggsn')->detail($sn_id);
		$map = array();
		$map['openid'] = $openid;
		$count = $objsn->where($map)->count();
		//K::M('weixin/goldeggsn')->items(array('openid'=>$openid),null,null,null,$count);
		
		$data['rid']		= intval($record['sn_id']);
		$data['sn']		= $record['sn'];
		$data['dateline']	= $record['dateline'];
		$data['usenums']	= $count;
		return $data;
	}

	protected function get_prize($Lottery){
		$id=intval($Lottery['id']);
		
		$firstNum=intval($Lottery['fistnums'])-intval($Lottery['fistlucknums']);
		$secondNum=intval($Lottery['secondnums'])-intval($Lottery['secondlucknums']);
		$thirdNum=intval($Lottery['thirdnums'])-intval($Lottery['thirdlucknums']);
		$fourthNum=intval($Lottery['fournums'])-intval($Lottery['fourlucknums']);
		$fifthNum=intval($Lottery['fivenums'])-intval($Lottery['fivelucknums']);
		$sixthNum=intval($Lottery['sixnums'])-intval($Lottery['sixlucknums']);
		$multi=intval($Lottery['max_num']);//最多抽奖次数
		$prize_arr = array(
			'0' => array('id'=>1,'prize'=>'一等奖','v'=>$firstNum,'start'=>0,'end'=>$firstNum), 
			'1' => array('id'=>2,'prize'=>'二等奖','v'=>$secondNum,'start'=>$firstNum,'end'=>$firstNum+$secondNum), 
			'2' => array('id'=>3,'prize'=>'三等奖','v'=>$thirdNum,'start'=>$firstNum+$secondNum,'end'=>$firstNum+$secondNum+$thirdNum),
			'3' => array('id'=>4,'prize'=>'四等奖','v'=>$fourthNum,'start'=>$firstNum+$secondNum+$thirdNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum),
			'4' => array('id'=>5,'prize'=>'五等奖','v'=>$fifthNum,'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum),
			'5' => array('id'=>6,'prize'=>'六等奖','v'=>$sixthNum,'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum,'end'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum),
			'6' => array('id'=>7,'prize'=>'谢谢参与','v'=>(intval($Lottery['predict_num']))*$multi-($firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum),'start'=>$firstNum+$secondNum+$thirdNum+$fourthNum+$fifthNum+$sixthNum,'end'=>intval($Lottery['predict_num'])*$multi)
		);
		//
		foreach ($prize_arr as $key => $val) { 
			$arr[$val['id']] = $val; 
		} 
		//-------------------------------	 
		//随机抽奖[如果预计活动的人数为1为各个奖项100%中奖]
		//-------------------------------	 
		if ($Lottery['predict_num'] == 1) {
	 
			if ($Lottery['fistlucknums'] <= $Lottery['fistnums']) {
				$prizetype = 1;	
			}else{
				$prizetype = 7;	
			}			
			 
		}else{
			$prizetype = $this->get_rand($arr,(intval($Lottery['predict_num'])*$multi)); 
		}
		 
		//$winprize = $prize_arr[$rid-1]['prize'];
		switch($prizetype){
			case 1:
					 
				if ($Lottery['fistlucknums'] >= $Lottery['fistnums']) {
					 $prizetype = ''; 
					 //$winprize = '谢谢参与'; 
				}else{
					 
					$prizetype = 1; 					
				    //$lottery_db->where(array('id'=>$id))->setInc('fistlucknums');
				}
			break;
				
			case 2:
				if ($Lottery['secondlucknums'] >= $Lottery['secondnums']) {
						$prizetype = ''; 
						//$winprize = '谢谢参与';
				}else{
					//判断是否设置了2等奖&&数量
					if(empty($Lottery['second']) && empty($Lottery['secondnums'])){
						$prizetype = ''; 
						//$winprize = '谢谢参与';
					}else{ //输出中了二等奖
						$prizetype = 2; 					
						//$lottery_db->where(array('id'=>$id))->setInc('secondlucknums');
					}	 
					
				}
				break;
							
			case 3:
				if ($Lottery['thirdlucknums'] >= $Lottery['thirdnums']) {
					 $prizetype = ''; 
					// $winprize = '谢谢参与';
				}else{
					if(empty($Lottery['third']) && empty($Lottery['thirdnums'])){
						 $prizetype = ''; 
						// $winprize = '谢谢参与';
					}else{
						$prizetype = 3; 					
						//$lottery_db->where(array('id'=>$id))->setInc('thirdlucknums');
					} 
					
				}
				break;
						
			case 4:
				if ($Lottery['fourlucknums'] >= $Lottery['fournums']) {
					  $prizetype =  ''; 
					// $winprize = '谢谢参与';
				}else{
					 if(empty($Lottery['four']) && empty($Lottery['fournums'])){
					   	$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					 }else{
					 	$prizetype = 4; 					
						//$lottery_db->where(array('id'=>$id))->setInc('fourlucknums');
					 }					
				}
			break;
			
			case 5:
				if ($Lottery['fivelucknums'] >= $Lottery['fivenums']) {
					 $prizetype =  ''; 
					 //$winprize = '谢谢参与';
				}else{
					if(empty($Lottery['five']) && empty($Lottery['fivenums'])){
						$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					}else{
						$prizetype = 5; 					
						//$lottery_db->where(array('id'=>$id))->setInc('fivelucknums');
					} 
				}
			break;
			
			case 6:
				if ($Lottery['sixlucknums'] >= $Lottery['sixnums']) {
					 $prizetype =  ''; 
					// $winprize = '谢谢参与';
				}else{
					 if(empty($Lottery['six']) && empty($Lottery['sixnums'])){
					 	$prizetype =  ''; 
					 	//$winprize = '谢谢参与';
					 }else{
					 	$prizetype = 6; 					
						//$lottery_db->where(array('id'=>$id))->setInc('sixlucknums');
					 }
					
				}
			break;
							
			default:
					$prizetype =  ''; 
					//$winprize = '谢谢参与';
					
					break;
		}
		if (intval($prizetype)&&$prizetype<7){
			//M('Lottery_record')->where(array('lid'=> $id,'wecha_id'=>$this->_get('wecha_id')))->save(array('isegg'=>1));
		}
		
		return $prizetype;
	}

	protected function get_rand($proArr,$total) {

		    $result = 7; 
		    $randNum = mt_rand(1, $total); 
		    foreach ($proArr as $k => $v) {
		    	
		    	if ($v['v']>0){//奖项存在或者奖项之外
		    		if ($randNum>$v['start']&&$randNum<=$v['end']){
		    			$result=$k;
		    			break;
		    		}
		    	}
		    }

		    return $result; 
	}

	
}

   