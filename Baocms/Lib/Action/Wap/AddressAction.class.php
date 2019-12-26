<?php
class AddressAction extends CommonAction{
   public function addlist() {
		$type = I('type', '', 'trim,htmlspecialchars');
		$tuan_id = isset($_GET['tuan_id']) ? intval($_GET['tuan_id']) : 0;
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$log_id = isset($_GET['log_id']) ? intval($_GET['log_id']) : 0;
		$addrcat = "http://" . $_SERVER['HTTP_HOST'] . U('address/addrcat', array('type' => $type, 'order_id' => $order_id));
		$ud = D('Paddress');
		$defaultadd = $ud -> where(array('user_id' => $this -> uid, 'default' => 1,'closed' => 0)) -> select();
		$addlist = $ud -> where(array('user_id' => $this -> uid, 'default' => 0,'closed' =>0)) -> order("id desc") -> select();
		$buyUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/mall/pay" . "/type/" . $type. "/order_id/" . $order_id . "/address_id/";
		
		if(empty($type) && empty($order_id)){
			$editUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/address/edit/address_id/";
			$delUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/address/delete/address_id/";
		}else{
			$editUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/address/addedit" . "/type/" . $type. "/order_id/" . $order_id . "/address_id/";
			$delUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/address/adddels". "/type/" . $type ."/order_id/" . $order_id . "/address_id/";
		}
		
		
		$this -> assign('addrcat', $addrcat);
		$this -> assign('defaultadd', $defaultadd);
		$this -> assign('addlist', $addlist);
		$this -> assign('buyUrl', $buyUrl);
		$this -> assign('editUrl', $editUrl);
		$this -> assign('delUrl', $delUrl);
		$this -> assign('type', $type);
		$this -> assign('order_id', $order_id);
		$this -> assign('log_id', $log_id);
		$this -> display();
	}
	//众筹商城选择地址
	 public function choice() {
		$type = I('type', '', 'trim,htmlspecialchars');
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$log_id = isset($_GET['log_id']) ? intval($_GET['log_id']) : 0;
		$address_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if($type == crowd && (!empty($address_id))){//众筹换地址
			if (false == D('Crowdorder') -> replace_crowd_pay_addr($order_id,$address_id,$this->uid)) {//更换众筹地址
				$this->fengmiMsg('更新地址出错');
			}else{
				$this->fengmiMsg('更换地址成功，正在跳转', U('crowd/pay', array('type' => $type,'order_id' => $order_id,'address_id'=>$address_id)));
			}
		}elseif($type == goods && (!empty($log_id))){//商城合并付款
			D('Ordergoods')->merge_update_express_price($this->uid,$type,$log_id,$address_id);//传4个参数
			$this->fengmiMsg('选择商城收货地址操作成功', U('mall/paycode', array('type' => $type,'log_id' => $log_id,'address_id'=>$id)));
		}elseif($type == goods && (!empty($order_id))){//商城单个付款
			D('Ordergoods')->update_express_price($this->uid,$type, $order_id,$address_id );
			$this->fengmiMsg('更换商城地址成功', U('mall/pay', array('type' => $type,'order_id' => $order_id,'address_id'=>$address_id)));
		} else {
			$this->fengmiMsg('操作失败');
		}
	}

	public function addrcat() {
		$type = I('type', '', 'trim,htmlspecialchars');
		$order_id = (int)$this -> _get('order_id');//order_id先暂时不去模板里面调用
		$goods_id = (int)$this -> _get('goods_id');
		$log_id = (int)$this -> _get('log_id');
		$pc_order = (int) $this->_get('pc_order');
		$category = (int) $this->_get('category');
	    if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), array('type','defaults','goods_id','log_id','category', 'addxm', 'addtel', 'province', 'city', 'areas', 'addinfo'));
			$data['type'] = htmlspecialchars($data['type']);
			$data['defaults'] = (int) $data['defaults'];
			$data['goods_id'] = (int) $data['goods_id'];
			$data['log_id'] = (int) $data['log_id'];
			$data['pc_order'] = (int) $data['pc_order'];
			$create_category = (int) $data['category'];//调用
			$data['addxm'] = htmlspecialchars($data['addxm']);
			$data['addtel'] = htmlspecialchars($data['addtel']);
			if (empty($data['addtel'])) {
				$this->fengmiMsg('联系电话不能为空');
			}
			if (!isPhone($data['addtel']) && !isMobile($data['addtel'])) {
				$this->fengmiMsg('联系电话格式不正确');
			}
			$data['province'] = (int) $data['province'];
			if (empty($data['province'])) {
				$this->fengmiMsg('省份不能为空');
			}
			$data['city'] = (int) $data['city'];
			if (empty($data['city'])) {
				$this->fengmiMsg('城市不能为空');
			}
			$data['areas'] = (int) $data['areas'];
			if (empty($data['areas'])) {
				$this->fengmiMsg('地区不能为空');
			}
			$data['addinfo'] = htmlspecialchars($data['addinfo']);
			if (empty($data['addinfo'])) {
				$this->fengmiMsg('详细地址不能为空');
			}
			$provinfo = D('Paddlist') -> find($data['province']);
			$cityinfo = D('Paddlist') -> find($data['city']);
			$areasinfo = D('Paddlist') -> find($data['areas']);
			$areainfo = D('Paddlist') -> find($data['addinfo']);
			
			$newadd = array(
				'user_id' => $this ->uid, 
				'default' => $data['defaults'], 
				'xm' => $data['addxm'], 
				'tel' => $data['addtel'], 
				'province_id' => $data['province'], 
				'city_id' => $data['city'], 
				'area_id' => $data['areas'], 
				'area_str' => $provinfo['name'] . " " . $cityinfo['name'] . " " . $areasinfo['name'] . " " . $areainfo['name'], 
				'info' => $data['addinfo']
			);
			
			$Paddress= D('Paddress');
			if ($data['defaults'] == 1) {
				$update_default_user_address = $Paddress -> where('user_id =' . $this -> uid) -> setField('default', 0);
			}
			if ($id = $Paddress->add($newadd)) {
				if($data['type'] == goods && (!empty($order_id))){//首先检测订单ID
					D('Ordergoods')->update_express_price($this->uid,$data['type'], $order_id,$id);
					$this->fengmiMsg('操作成功', U('mall/pay', array('type' => $data['type'],'order_id' => $order_id,'address_id'=>$id)));
				}elseif($data['type'] == goods && (!empty($create_category))){
					$this->fengmiMsg('添加地址成功，正在为你返回购物车', U('mall/cart'));
				}elseif($data['type'] == goods && (!empty($data['log_id']))){
					D('Ordergoods')->merge_update_express_price($this->uid,$data['type'],$data['log_id'],$id);//传4个参数，合并付款封装修改运费
					$this->fengmiMsg('合并付款添加地址成功', U('mall/paycode', array('type' => $data['type'],'log_id' => $data['log_id'],'address_id'=>$id)));
				}elseif($data['type'] == crowd && (!empty($data['goods_id']))){
					$this->fengmiMsg('添加众筹地址成功，正在为您返回', U('crowd/detail',array('goods_id'=>$data['goods_id'])));
				}elseif($data['type'] == crowd && (!empty($order_id))){
					if (false == D('Crowdorder')->replace_crowd_pay_addr($order_id,$id,$this->uid)) {
						$this->fengmiMsg('更新地址出错');
					}else{
						$this->fengmiMsg('新增地址成功，正在跳转', U('crowd/pay', array('type' =>$data['type'],'order_id' =>$order_id,'address_id'=>$id)));
					}
				}else{
					$this->fengmiMsg('添加收货地址成功', U('user/goods/index', array('aready' =>1)));
				}
			} else {
				$this->fengmiMsg('操作失败');
			}
		}else{
			$provinceList = D('Paddlist') -> where(array('level' => 1)) -> select();
			$this -> assign('provinceList', $provinceList);
			$this -> assign('buyUrl', $buyUrl);
			$this -> assign('type', $type);
			$this -> assign('order_id', $order_id);
			$this -> assign('goods_id', $goods_id);
			$this -> assign('log_id', $log_id);
			$this -> assign('pc_order', $pc_order);
			$this -> assign('category', $category);
			$this -> display();
		}
		
	}
	//修改收货地址重写
	public function addedit() {
		$type = I('type', '', 'trim,htmlspecialchars');
		$order_id = (int)$this -> _get('order_id');
		$goods_id = (int)$this -> _get('goods_id');
		$log_id = (int)$this -> _get('log_id');
		$address_id = (int)$this -> _get('address_id');
		$detail = D('Paddress') -> where(array('user_id' => $this -> uid, 'id' => $address_id)) -> find();
		if(empty($detail) || empty($address_id) || empty($order_id)){
			$this->error('存在异常，请找稍后再试');
		}
	    if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), array('type','defaults','addxm', 'addtel', 'province', 'city', 'areas', 'addinfo'));
			$data['type'] = htmlspecialchars($data['type']);
			$data['defaults'] = (int) $data['defaults'];
			$data['addxm'] = htmlspecialchars($data['addxm']);
			$data['addtel'] = htmlspecialchars($data['addtel']);
			if (empty($data['addtel'])) {
				$this->fengmiMsg('联系电话不能为空');
			}
			if (!isPhone($data['addtel']) && !isMobile($data['addtel'])) {
				$this->fengmiMsg('联系电话格式不正确');
			}
			$data['province'] = (int) $data['province'];
			if (empty($data['province'])) {
				$this->fengmiMsg('省份不能为空');
			}
			$data['city'] = (int) $data['city'];
			if (empty($data['city'])) {
				$this->fengmiMsg('城市不能为空');
			}
			$data['areas'] = (int) $data['areas'];
			if (empty($data['areas'])) {
				$this->fengmiMsg('地区不能为空');
			}
			$data['addinfo'] = htmlspecialchars($data['addinfo']);
			if (empty($data['addinfo'])) {
				$this->fengmiMsg('详细地址不能为空');
			}
			$provinfo = D('Paddlist') -> find($data['province']);
			$cityinfo = D('Paddlist') -> find($data['city']);
			$areasinfo = D('Paddlist') -> find($data['areas']);
			$areainfo = D('Paddlist') -> find($data['addinfo']);
			
			$newadd = array(
				'id' => $address_id,
				'user_id' => $this ->uid, 
				'default' => $data['defaults'], 
				'xm' => $data['addxm'], 
				'tel' => $data['addtel'], 
				'province_id' => $data['province'], 
				'city_id' => $data['city'], 
				'area_id' => $data['areas'], 
				'area_str' => $provinfo['name'] . " " . $cityinfo['name'] . " " . $areasinfo['name'] . "  " . $areainfo['name'], 
				'info' => $data['addinfo']
			);
			
			$Paddress= D('Paddress');
			if ($data['defaults'] == 1) {
				$update_default_user_address = $Paddress -> where('user_id =' . $this -> uid) -> setField('default', 0);
			}
			if ($Paddress->save($newadd)) {
				if($data['type'] == goods && (!empty($address_id))){//如果是商城
					D('Ordergoods')->update_express_price($this->uid,$data['type'], $order_id,$address_id);//去更新订单运费
					$this->fengmiMsg('修改成功', U('mall/pay', array('type' => $data['type'],'order_id' => $order_id,'address_id'=>$address_id)));
				}elseif($data['type'] == goods && (!empty($data['log_id']))){
					D('Ordergoods')->merge_update_express_price($this->uid,$data['type'],$log_id,$address_id);//传4个参数，合并付款封装修改运费
					$this->fengmiMsg('修改合并付款地址成功', U('mall/paycode', array('type' => $data['type'],'log_id' =>$log_id,'address_id'=>$address_id)));
				}elseif($data['type'] == crowd && (!empty($address_id))){//如果是众筹
					if (false == D('Crowdorder')->replace_crowd_pay_addr($order_id,$address_id,$this->uid)) {//更换众筹地址
						$this->fengmiMsg('更新地址出错');
					}else{
						$this->fengmiMsg('编辑地址成功，正在跳转', U('crowd/pay', array('type' =>$data['type'],'order_id' =>$order_id,'address_id'=>$address_id)));
					}
				}
				
			} else {
				$this->fengmiMsg('修改失败，请重试');
			}
			
		}else{
			$provinceList = D('Paddlist') -> where(array('level' => 1)) -> select();
			$this -> assign('provinceList', $provinceList);
			$cityList = D('Paddlist') -> where(array('upid' => $detail['province_id'])) -> select();
			$areaList = D('Paddlist') -> where(array('upid' => $detail['city_id'])) -> select();
			$this -> assign('cityList', $cityList);
			$this -> assign('areaList', $areaList);
			$this -> assign('detail', $detail);
			$this -> assign('type', $type);
			$this -> assign('order_id', $order_id);
			$this -> assign('goods_id', $goods_id);
			$this -> assign('log_id', $log_id);
			$this -> assign('address_id', $address_id);
			$this -> display();
		}
		
	}
	
	//更新收货地址
	public function edit() {
		$address_id = (int)$this -> _get('address_id');
		$detail = D('Paddress') -> where(array('user_id' => $this -> uid, 'id' => $address_id)) -> find();
		if(empty($address_id)){
			$this->error('您选择的地址不存在');
		}
	    if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), array('type','defaults','addxm', 'addtel', 'province', 'city', 'areas', 'addinfo'));
			$data['addxm'] = htmlspecialchars($data['addxm']);
			$data['addtel'] = htmlspecialchars($data['addtel']);
			if (empty($data['addtel'])) {
				$this->fengmiMsg('联系电话不能为空');
			}
			if (!isPhone($data['addtel']) && !isMobile($data['addtel'])) {
				$this->fengmiMsg('联系电话格式不正确');
			}
			$data['province'] = (int) $data['province'];
			if (empty($data['province'])) {
				$this->fengmiMsg('省份不能为空');
			}
			$data['city'] = (int) $data['city'];
			if (empty($data['city'])) {
				$this->fengmiMsg('城市不能为空');
			}
			$data['areas'] = (int) $data['areas'];
			if (empty($data['areas'])) {
				$this->fengmiMsg('地区不能为空');
			}
			$data['addinfo'] = htmlspecialchars($data['addinfo']);
			if (empty($data['addinfo'])) {
				$this->fengmiMsg('详细地址不能为空');
			}
			$provinfo = D('Paddlist') -> find($data['province']);
			$cityinfo = D('Paddlist') -> find($data['city']);
			$areasinfo = D('Paddlist') -> find($data['areas']);
			$areainfo = D('Paddlist') -> find($data['addinfo']);
			$newadd = array(
				'id' => $address_id,
				'user_id' => $this ->uid, 
				'default' => $data['defaults'], 
				'xm' => $data['addxm'], 
				'tel' => $data['addtel'], 
				'province_id' => $data['province'], 
				'city_id' => $data['city'], 
				'area_id' => $data['areas'], 
				'area_str' => $provinfo['name'] . " " . $cityinfo['name'] . " " . $areasinfo['name'] . "  " . $areainfo['name'], 
				'info' => $data['addinfo']
			);
			$Paddress= D('Paddress');
			if ($data['defaults'] == 1) {
				$update_default_user_address = $Paddress -> where('user_id =' . $this -> uid) -> setField('default', 0);
			}
			if ($Paddress->save($newadd)) {
				$this->fengmiMsg('编辑地址成功，正在跳转', U('address/addlist'));
			} else {
				$this->fengmiMsg('修改失败，请重试');
			}
			
		}else{
			$provinceList = D('Paddlist') -> where(array('level' => 1)) -> select();
			$this -> assign('provinceList', $provinceList);
			$cityList = D('Paddlist') -> where(array('upid' => $detail['province_id'])) -> select();
			$areaList = D('Paddlist') -> where(array('upid' => $detail['city_id'])) -> select();
			$this -> assign('cityList', $cityList);
			$this -> assign('areaList', $areaList);
			$this -> assign('detail', $detail);
			$this -> assign('address_id', $address_id);
			$this -> display();
		}
		
	}
	//删除地址
	public function adddels() {
		$type = I('type', '', 'trim,htmlspecialchars');
		$address_id = (int)$this -> _get('address_id');
		$tuan_id = isset($_GET['tuan_id']) ? intval($_GET['tuan_id']) : 0;
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$obj = D('Paddress');
		if ($obj -> delete($address_id)) {
			$this -> success('删除成功！', U('address/addlist', array('type' => $type, 'order_id' => $order_id)));

		}
		$this -> display();
	}
	//删除地址会员中心直接删除的时候
	public function delete() {
		$address_id = (int)$this -> _get('address_id');
		$obj = D('Paddress');
		if ($obj->save(array('id' => $address_id, 'closed' => 1))) {
			$this -> error('删除成功！', U('address/addlist'));

		}
	}
	//获取城市ID
	public function city() {
		$upid = isset($_GET['upid']) ? intval($_GET['upid']) : 0;
		$callback = $_GET['callback'];
		$outArr = array();
		$cityList = D('Paddlist') -> where(array('upid' => $upid)) -> select();
		if (is_array($cityList) && !empty($cityList)) {
			foreach ($cityList as $key => $value) {
				$outArr[$key]['id'] = $value['id'];
				$outArr[$key]['name'] = $value['name'];
			}
		}
		$outStr = '';
		$outStr = json_encode($outArr);
		if ($callback) {
			$outStr = $callback . "(" . $outStr . ")";
		}
		echo $outStr;
		die();
	}
}