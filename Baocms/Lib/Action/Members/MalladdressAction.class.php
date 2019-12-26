<?php
class MalladdressAction extends CommonAction{
    public function index(){
        $Paddress = D('Paddress');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid, 'closed' => 0);
        $count = $Paddress->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Paddress->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
   
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
   public function create() {
		$type = I('type', '', 'trim,htmlspecialchars');
		$order_id = (int)$this -> _get('order_id');
		$pc_order = (int) $this->_get('pc_order');
		$category = (int) $this->_get('category');
		$goods_id = (int) $this->_get('goods_id');

	    if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), array('type','defaults','pc_order','category','goods_id','addxm', 'addtel', 'province', 'city', 'areas', 'addinfo'));
			$data['type'] = htmlspecialchars($data['type']);
			$data['defaults'] = (int) $data['defaults'];
			$data['pc_order'] = (int) $data['pc_order'];
			$create_category = (int) $data['category'];
			$create_order_id = (int) $data['order_id'];
			$create_goods_id = (int) $data['goods_id'];
			$data['addxm'] = htmlspecialchars($data['addxm']);
			$data['addtel'] = htmlspecialchars($data['addtel']);
			if (empty($data['addtel'])) {
				$this->baoError('联系电话不能为空');
			}
			if (!isPhone($data['addtel']) && !isMobile($data['addtel'])) {
				$this->baoError('联系电话格式不正确');
			}
			$data['province'] = (int) $data['province'];
			if (empty($data['province'])) {
				$this->baoError('省份不能为空');
			}
			$data['city'] = (int) $data['city'];
			if (empty($data['city'])) {
				$this->baoError('城市不能为空');
			}
			$data['areas'] = (int) $data['areas'];
			if (empty($data['areas'])) {
				$this->baoError('地区不能为空');
			}
			$data['addinfo'] = htmlspecialchars($data['addinfo']);
			if (empty($data['addinfo'])) {
				$this->baoError('详细地址不能为空');
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
				'area_str' => $provinfo['name'] . " " . $cityinfo['name'] . "  " . $areasinfo['name'] . "" . $areainfo['name'], 
				'info' => $data['addinfo']
			);
			
			$Paddress= D('Paddress');
			if ($data['defaults'] == 1) {
				$update_default_user_address = $Paddress -> where('user_id =' . $this -> uid) -> setField('default', 0);
			}
			if ($id = $Paddress->add($newadd)) {
				if($data['type'] == goods && (!empty($id))){//商城类型
					D('Ordergoods')->update_express_price($this->uid,$data['type'], $order_id,$id);//去更新订单运费
					if(!empty($create_order_id)){//首先检测订单ID
						$this->baoSuccess('添加成功', U('home/mall/pay', array('type' => $data['type'],'order_id' => $create_order_id,'address_id'=>$id)));
					}elseif(!empty($create_goods_id)){//再检测商品ID
						$this->baoSuccess('添加成功，正在为您返回', U('home/mall/detail', array('goods_id' =>$create_goods_id)));
					}elseif(!empty($create_category)){//再检测购物车路径
						$this->baoSuccess('添加地址成功，正在为你返回购物车', U('home/mall/cart'));
					}else{
						$this->baoSuccess('添加地址操作成功', U('malladdress/index'));
					}
				}elseif($data['type'] == crowd && (!empty($id))){//众筹类型
					$this->baoSuccess('添加众筹地址成功，正在为您返回', U('home/crowd/detail', array('goods_id' =>$create_goods_id)));
				}else{
					$this->baoSuccess('添加地址操作成功，但是没识别到类型', U('malladdress/index'));
				}
			} else {
				$this->baoError('操作失败');
			}
			
		}else{
			$provinceList = D('Paddlist') -> where(array('level' => 1)) -> select();
			$this -> assign('provinceList', $provinceList);
			$this -> assign('buyUrl', $buyUrl);
			$this -> assign('type', $type);
			$this -> assign('order_id', $order_id);
			$this -> assign('goods_id', $goods_id);
			$this -> assign('pc_order', $pc_order);
			$this -> assign('category', $category);
			$this -> display();
		}
		
	}
	//修改重写
	public function edit() {
		$type = I('type', '', 'trim,htmlspecialchars');
		$order_id = (int)$this -> _get('order_id');
		$id = (int)$this -> _get('id');
		$detail = D('Paddress') -> where(array('user_id' =>$this ->uid, 'id' => $id)) -> find();
		if(empty($detail) || empty($id)){
			$this->error('存在异常，请找稍后再试');
		}
		
	    if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), array('type','defaults', 'addxm', 'addtel', 'province', 'city', 'areas', 'addinfo'));
			$data['type'] = htmlspecialchars($data['type']);
			$data['defaults'] = (int) $data['defaults'];
			$data['addxm'] = htmlspecialchars($data['addxm']);
			$data['addtel'] = htmlspecialchars($data['addtel']);
			if (empty($data['addtel'])) {
				$this->baoError('联系电话不能为空');
			}
			if (!isPhone($data['addtel']) && !isMobile($data['addtel'])) {
				$this->baoError('联系电话格式不正确');
			}
			$data['province'] = (int) $data['province'];
			if (empty($data['province'])) {
				$this->baoError('省份不能为空');
			}
			$data['city'] = (int) $data['city'];
			if (empty($data['city'])) {
				$this->baoError('城市不能为空');
			}
			$data['areas'] = (int) $data['areas'];
			if (empty($data['areas'])) {
				$this->baoError('地区不能为空');
			}
			$data['info'] = htmlspecialchars($data['addinfo']);
			if (empty($data['info'])) {
				$this->baoError('详细地址不能为空');
			}
			$provinfo = D('Paddlist') -> find($data['province']);
			$cityinfo = D('Paddlist') -> find($data['city']);
			$areasinfo = D('Paddlist') -> find($data['areas']);
			$areainfo = D('Paddlist') -> find($data['addinfo']);
			
			$newadd = array(
				'id' => $id,
				'user_id' => $this ->uid, 
				'default' => $data['defaults'], 
				'xm' => $data['addxm'], 
				'tel' => $data['addtel'], 
				'province_id' => $data['province'], 
				'city_id' => $data['city'], 
				'area_id' => $data['areas'], 
				'area_str' => $provinfo['name'] . " " . $cityinfo['name'] . "" . $areasinfo['name'] . "  " . $areainfo['name'], 
				'info' => $data['addinfo']
			);
			
			$Paddress= D('Paddress');
			if ($data['defaults'] == 1) {
				$update_default_user_address = $Paddress -> where('user_id =' . $this -> uid) -> setField('default', 0);
			}
			
			if ($Paddress->save($newadd)) {
				if(empty($order_id)){
					$this->baoSuccess('修改收获地址成功', U('malladdress/index'));
				}else{
					D('Ordergoods')->update_express_price($this->uid,$data['type'], $order_id,$id);//去更新订单运费
					$this->baoSuccess('修改成功', U('home/mall/pay', array('type' => $data['type'],'order_id' => $order_id,'address_id'=>$id)));
				}
				
			} else {
				$this->baoError('修改失败，请重试');
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
			$this -> assign('id', $id);
			$this -> display();
		}
		
	}
	//删除
    public function delete($id){
        $id = (int) $id;
        if (empty($id)) {
            $this->baoError('收货地址不存在');
        }
        if (!($detail = D('Paddress')->find($id))) {
            $this->baoError('收货地址不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->baoError('请不要操作别人的收货地址');
        }
        $obj = D('Paddress');
        $obj->save(array('id' => $id, 'closed' => 1));
        $this->baoSuccess('删除成功！', U('malladdress/index'));
    }
  
}