<?php
class AddressAction extends CommonAction{
	
  	 public function add_address() {
        if (empty($this->uid)) {
            $this->ajaxLogin();
        }
        $data['user_id'] = $this->uid;
        if (IS_AJAX) {
            $data['addxm'] = htmlspecialchars($_POST['addxm']);
            if (empty($data['addxm'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '联系人不能为空！'));
            }
            $data['province'] = (int) $_POST['province'];
            if (empty($data['province'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '省份不能为空！'));
            }
            $data['city'] = (int) $_POST['city'];
            if (empty($data['city'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '地区不能为空！'));
            }
            $data['areas'] = (int) $_POST['areas'];
            if (empty($data['areas'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '商圈不能为空！'));
            }
            $data['addtel'] = htmlspecialchars($_POST['addtel']);
            if (empty($data['addtel'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码不能为空！'));
            }
            if (!isMobile($data['addtel'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码格式不正确！'));
            }
            $data['addinfo'] = htmlspecialchars($_POST['addinfo']);
            if (empty($data['addinfo'])) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '地址不能为空！'));
            }
            $order_id = (int)$_POST['order_id'];
			$data['defaults'] = (int)$_POST['defaults'];
			$data['type'] = htmlspecialchars($_POST['type']);
			
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
				'area_str' => $provinfo['name'] . " " . $cityinfo['name'] . " " . $areasinfo['name'] . "" . $areainfo['name'], 
				'info' => $data['addinfo']
			);
			
			$Paddress= D('Paddress');
			if ($data['defaults'] == 1) {
				$update_default_user_address = $Paddress -> where('user_id =' . $this -> uid) -> setField('default', 0);
			}
			
			if ($id = $Paddress->add($newadd)) {
				if($data['type'] == goods && (!empty($id))){
					D('Ordergoods')->update_express_price($this->uid,$data['type'], $order_id,$id);//去更新订单运费
				}elseif($data['type'] == crowd && (!empty($id))){
					$this->ajaxReturn(array('status' => 'success', 'msg' => '添加成功！', U('crowd/pay', array('order_id' => $order_id,'address_id'=>$id))));
				}else{
					$this->ajaxReturn(array('status' => 'error', 'msg' => '没有获取到当前类型'));
				}
				$this->ajaxReturn(array('status' => 'success', 'msg' => '添加地址成功！', U('mall/pay', array('type' => $data['type'],'order_id' => $order_id,'address_id'=>$id))));
			} else {
				$this->ajaxReturn(array('status' => 'error', 'msg' => '添加失败！'));
			}
        } else {
            $this->display();
        }
    }
    //众筹+商城的地址更换
	public function change_addr(){
        if (IS_AJAX) {
            $order_id = (int) $_POST['order_id'];
            $id = (int) $_POST['id'];//获取地址表
			$type = htmlspecialchars($_POST['type']);
            $data = array('order_id' => $order_id, 'address_id' => $id);//更新订单表
            if (false !== D('Order')->save($data)) {
                $thisaddr = D('Paddress')->find($id);
                $addrs = D('Paddress')->where(array('user_id' => $this->uid, 'id' => array('NEQ', $id)))->order('id DESC')->limit(0, 4)->select();
                if (empty($addrs)) {
                    $addrs[] = $thisaddr;
                } else {
                    array_unshift($addrs, $thisaddr);
                }
                $addr_array = array();
                foreach ($addrs as $k => $val) {
                    $addr_array[$k]['id'] = $val['id'];
                    $addr_array[$k]['province_id'] = $val['province_id'];
                    $addr_array[$k]['city_id'] = $val['city_id'];
                    $addr_array[$k]['area_id'] = $val['area_id'];
                    $addr_array[$k]['info'] = $val['info'];
                    $addr_array[$k]['tel'] = $val['tel'];
                    $addr_array[$k]['xm'] = $val['xm'];
                }
				
				if($type == goods && (!empty($id))){
					 D('Ordergoods')->update_express_price($this->uid,$data['type'], $order_id,$id);//去更新订单运费
					 $this->ajaxReturn(array('status' => 'success', 'msg' => '更换成功', 'res' => $addr_array));
				}elseif($type == crowd && (!empty($id))){//如果是众筹
					$this->ajaxReturn(array('status' => 'success', 'msg' => '更换成功', 'res' => $addr_array, U('crowd/pay', array('order_id' => $order_id,'address_id'=>$id))));
				}else{
					 $this->ajaxReturn(array('status' => 'error', 'msg' => '更换失败'));
				}
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '更换失败'));
            }
        }
    }


    //商城合并付款更换地址
	public function paycode_change_addr(){
        if (IS_AJAX) {
            $log_id = (int) $_POST['log_id'];
            $id = (int) $_POST['id'];//获取地址表
			$type = htmlspecialchars($_POST['type']);
			
			
		    if(false == D('Paddress')->paycode_replace_default_address($this->uid,$id )) {//合并付款更换默认地址
             	$this->ajaxReturn(array('status' => 'error', 'msg' => '默认地址数据库更换失败'));
			}else{
				if (false== D('Ordergoods')->merge_update_express_price($this->uid,$type,$log_id,$id)) {
					$this->ajaxReturn(array('status' => 'error', 'msg' => '更换失败'));
				}else{
					$this->ajaxReturn(array('status' => 'success', 'msg' => '更换成功', 'res' => $addr_array, U('mall/paycode', array('log_id' => $log_id,'address_id'=>$id))));
				}
			}
        }
    }
}