<?php
class  AppointAction extends CommonAction{
	 
	 private $create_fields = array('shop_id','cate_id','city_id','lng','lat','price', 'title', 'intro', 'unit','gongju', 'photo','thumb', 'user_name','user_mobile', 'biz_time','end_date','contents');
    private $edit_fields = array('shop_id','cate_id','city_id','lng','lat','price', 'title', 'intro', 'unit','gongju', 'photo','thumb', 'user_name','user_mobile', 'biz_time','end_date','contents');
	 private $worker_edit_fields = array('appoint_id','price','photo','name','office','mobile', 'content', 'audit');
	
	public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['appoint'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
	
	
	
	 public function index(){
        $Appoint = D('Appoint');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'shop_id'=>$this->shop_id);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
		
        if ($keyword) {
            $map['title|intro'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }  
		
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Appointcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
		
        $count = $Appoint->where($map)->count(); 
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $list = $Appoint->where($map)->order(array('appoint_id' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		$shop_ids = array();
        foreach ($list as $key => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }		
		
        $this->assign('list', $list); 
		$this->assign('cates', D('Appointcate')->fetchAll());
        $this->assign('page', $show); 
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->display(); 
    }

	//添加家政
	public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Appoint');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('Appoint/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Appointcate')->fetchAll());
            $this->display();
        }
    }
	//添加验证
	 private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		
			$data['shop_id'] = $this->shop_id;//商家ID
			if (empty($data['shop_id'])) {
            $this->baoError('请您选择商家');
     	    } 
			$shop = D('Shop')->find($data['shop_id']);
			if (empty($shop)) {
				$this->baoError('请选择正确的商家');
			}
			$data['city_id'] = $shop['city_id'];
			$data['area_id'] = $shop['area_id'];
			$data['business_id'] = $shop['business_id'];
			$data['lng'] = $shop['lng'];
			$data['lat'] = $shop['lat'];
        	$data['cate_id'] = (int) $data['cate_id'];//ID
			if (empty($data['cate_id'])) {
				$this->baoError('类型ID不能为空');
			}
			$Appointcate = D('Appointcate')->where(array('cate_id' => $data['cate_id']))->find();
			$parent_id = $Appointcate['parent_id'];
			if ($parent_id == 0) {
			$this->baoError('请选择二级分类');
			}
			
            $data['title'] = htmlspecialchars($data['title']);
			if (empty($data['title'])) {
            $this->baoError('请您填写服务标题');
     	    }
			if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->baoError('标题内容含有敏感词：' . $words);
			}
			$data['intro'] = htmlspecialchars($data['intro']);//标题名字
			if (empty($data['intro'])) {
            $this->baoError('请您填写服务建议');
     	    }
			if ($words = D('Sensitive')->checkWords($data['intro'])) {
				$this->baoError('服务建议含有敏感词：' . $words);
			}
            $data['price'] = (int)($data['price'] * 100);
			if (empty($data['price'])) {
            $this->baoError('价格不能为空');
            }
            $data['unit']  = htmlspecialchars($data['unit']);
            $data['gongju']  = htmlspecialchars($data['gongju']);
			$data['user_name'] = htmlspecialchars($data['user_name']);
			if (empty($data['user_name'])) {
            $this->baoError('请您填写姓名');
     	    }
			$data['user_mobile'] = htmlspecialchars($data['user_mobile']);
			if (empty($data['user_mobile'])) {
            $this->baoError('请您手机号码');
     	    }
			if (!isPhone($data['user_mobile']) && !isMobile($data['user_mobile'])) {
            $this->baoError('联系电话格式不正确');
            }
			
            $data['photo']  = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
            $this->baoError('请您上传图片');
            }
			
			$thumb = $this->_param('thumb', false);
			foreach ($thumb as $k => $val) {
				if (empty($val)) {
					unset($thumb[$k]);
				}
				if (!isImage($val)) {
					unset($thumb[$k]);
				}
			}
			$data['thumb'] = serialize($thumb);
            $data['biz_time']  = htmlspecialchars($data['biz_time']);
			$data['end_date'] = htmlspecialchars($data['end_date']);
			if (empty($data['end_date'])) {
				$this->baoError('结束时间不能为空');
			}
			if (!isDate($data['end_date'])) {
				$this->baoError('结束时间格式不正确');
			}
			$data['contents'] = SecurityEditorHtml($data['contents']);
			if (empty($data['contents'])) {
            $this->baoError('家政内容不能为空');
			}
			if ($words = D('Sensitive')->checkWords($data['contents'])) {
				$this->baoError('家政简介含有敏感词：' . $words);
			}
        return $data;
    }
	
	 public function edit($appoint_id = 0){
        if ($appoint_id = (int) $appoint_id) {
            $obj = D('Appoint');
            if (!($detail = $obj->find($appoint_id))) {
                $this->baoError('请选择要编辑的活动');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['appoint_id'] = $appoint_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('appoint/index'));
                }
                $this->baoError('操作失败');
            } else {
                $thumb = unserialize($detail['thumb']);
				$this->assign('thumb', $thumb);
				$this->assign('cates', D('Appointcate')->fetchAll());
				$this->assign('shops', D('Shop')->find($detail['shop_id']));
				$this->assign('detail', $detail);
				$this->display();
            }
        } else {
            $this->baoError('请选择要编辑的家政');
        }
    }
    
	
	 private function editCheck(){
			$data = $this->checkFields($this->_post('data', false), $this->edit_fields);
			$data['shop_id'] =  $this->shop_id;//商家ID
			if (empty($data['shop_id'])) {
            $this->baoError('请您选择商家');
     	    } 
			$shop = D('Shop')->find($data['shop_id']);
			if (empty($shop)) {
				$this->baoError('请选择正确的商家');
			}
			$data['city_id'] = $shop['city_id'];
			$data['area_id'] = $shop['area_id'];
			$data['business_id'] = $shop['business_id'];
			$data['lng'] = $shop['lng'];
			$data['lat'] = $shop['lat'];
        	$data['cate_id'] = (int) $data['cate_id'];//ID
			if (empty($data['cate_id'])) {
				$this->baoError('类型ID不能为空');
			}
			$Appointcate = D('Appointcate')->where(array('cate_id' => $data['cate_id']))->find();
			$parent_id = $Appointcate['parent_id'];
			if ($parent_id == 0) {
			$this->baoError('请选择二级分类');
			}
			
            $data['title'] = htmlspecialchars($data['title']);
			if (empty($data['title'])) {
            $this->baoError('请您填写服务标题');
     	    }
			if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->baoError('标题内容含有敏感词：' . $words);
			}
			$data['intro'] = htmlspecialchars($data['intro']);//标题名字
			if (empty($data['intro'])) {
            $this->baoError('请您填写服务建议');
     	    }
			if ($words = D('Sensitive')->checkWords($data['intro'])) {
				$this->baoError('服务建议含有敏感词：' . $words);
			}
            $data['price'] = (int)($data['price'] * 100);
			if (empty($data['price'])) {
            $this->baoError('价格不能为空');
            }
            $data['unit']  = htmlspecialchars($data['unit']);
            $data['gongju']  = htmlspecialchars($data['gongju']);
			$data['user_name'] = htmlspecialchars($data['user_name']);
			if (empty($data['user_name'])) {
            $this->baoError('请您填写姓名');
     	    }
			$data['user_mobile'] = htmlspecialchars($data['user_mobile']);
			if (empty($data['user_mobile'])) {
            $this->baoError('请您手机号码');
     	    }
			if (!isPhone($data['user_mobile']) && !isMobile($data['user_mobile'])) {
            $this->baoError('联系电话格式不正确');
            }
			
            $data['photo']  = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
            $this->baoError('请您上传图片');
            }
			
			$thumb = $this->_param('thumb', false);
			foreach ($thumb as $k => $val) {
				if (empty($val)) {
					unset($thumb[$k]);
				}
				if (!isImage($val)) {
					unset($thumb[$k]);
				}
			}
			$data['thumb'] = serialize($thumb);
            $data['biz_time']  = htmlspecialchars($data['biz_time']);
			$data['end_date'] = htmlspecialchars($data['end_date']);
			if (empty($data['end_date'])) {
				$this->baoError('结束时间不能为空');
			}
			if (!isDate($data['end_date'])) {
				$this->baoError('结束时间格式不正确');
			}
            $data['contents'] = SecurityEditorHtml($data['contents']);
			if (empty($data['contents'])) {
            $this->baoError('家政内容不能为空');
			}
			if ($words = D('Sensitive')->checkWords($data['contents'])) {
				$this->baoError('家政简介含有敏感词：' . $words);
			}
        	return $data;
    }
	
   //家政删除
	public function delete($appoint_id = 0) {
        if (is_numeric($appoint_id) && ($appoint_id = (int) $appoint_id)) {
            $obj = D('Appoint');
            $obj->save(array('appoint_id' => $appoint_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('appoint/index'));
        } else {
            $appoint_id = $this->_post('appoint_id', false);
            if (is_array($appoint_id)) {
                $obj = D('Appoint');
                foreach ($appoint_id as $appoint_id) {
                    $obj->save(array('appoint_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('批量删除成功！', U('appoint/index'));
            }
            $this->baoError('请选择要删除的预约项目');
        }
    }
	
	//技师列表，由于数量不多，暂时不做分页
	public function worker($appoint_id = 0) {
        if ($appoint_id = (int) $appoint_id) {
			$Appoint = D('Appoint');
            $Appointworker = D('Appointworker');
			$detail = $Appoint->find($appoint_id);
			$this->assign('detail', $detail);
			$this->assign('list', $list = $Appointworker->where(array('appoint_id' => $appoint_id,'closed'=>0))->select());
			$this->display();
        } else {
            $this->error('请选择要设置的家政');
        }
    }
    //添加技师
	public function worker_create($appoint_id){
		if ($appoint_id = (int) $appoint_id) {
			$Appoint = D('Appoint');
            $Appointworker = D('Appointworker');
            if (!$detail = $Appoint->find($appoint_id)) {
                $this->error('请选择要设置的家政');
            }
            if ($detail['closed'] != 0) {
                $this->error('该家政已被删除');
            }
			if ($data = $this->_post('data', false)) {
				 
				if (!empty($data['price']) && !empty($data['name']) && !empty($data['mobile']) && !empty($data['intro'])){
					$Appointworker->add(array(
						'appoint_id' => $appoint_id,
						'price' => $data['price']*100,
						'photo' => $data['photo'],
						'name' => $data['name'],
						'office' => $data['office'],
						'intro' => $data['intro'],
						'mobile' => $data['mobile'],
						'content' => $data['content'],
						'audit' => 1,
						'create_time' => time(),
						'create_ip' => get_client_ip()
					));
					$this->baoSuccess('操作成功', U('appoint/worker',array('appoint_id'=>$detail['appoint_id'])));
				}else{
					 $this->baoError('内容不能为空');
				}
			}else{
				$this->assign('detail', $detail);
				$this->display();
			}

		} else {
            $this->error('请选择要设置的家政');
        }
	}
	
	
    //编辑技师
	public function worker_edit($worker_id){
		if ($worker_id = (int) $worker_id) {
			$Appoint = D('Appoint');
            $Appointworker = D('Appointworker');
			if (!$worker = $Appointworker->find($worker_id)) {
                $this->baoError('修改的内容不存在');
            }
			$appoint_id = $worker['appoint_id'];
            if (!$detail = $Appoint->find($appoint_id)) {
                $this->baoError('请选择要设置的家政');
            }
            if ($detail['closed'] != 0) {
                $this->baoError('该家政已被删除');
            }	
			
			if ($this->isPost()) {
                $data = $this->worker_editCheck();
				$data['worker_id'] = $worker_id;
				$data['appoint_id'] = $appoint_id;//商家ID
				if (false !==  $Appointworker->save($data)) {
					$this->baoSuccess('操作成功', U('appoint/worker',array('appoint_id'=>$detail['appoint_id'])));
				}else{
					$this->baoError('操作失败');
				} 
            } else {
               $this->assign('worker', $worker);//输出
				$this->assign('detail', $detail);
				$this->display();
            }
		} else {
            $this->baoError('修改的内容不存在');
        }
	}
	 private function worker_editCheck(){
			$data = $this->checkFields($this->_post('data', false), $this->worker_edit_fields);
            $data['price'] = htmlspecialchars($data['price'])*100;
			if (empty($data['price'])) {
            	$this->baoError('请填写价格');
     	    }
			$data['photo']  = htmlspecialchars($data['photo']);
			if (empty($data['photo'])) {
            	$this->baoError('请您上传头像');
            }
			$data['name'] = htmlspecialchars($data['name']);
			if (empty($data['name'])) {
            	$this->baoError('请填写姓名');
     	    }
			$data['office'] = htmlspecialchars($data['office']);
			if (empty($data['office'])) {
            	$this->baoError('请填写职位');
     	    }
			$data['mobile'] = htmlspecialchars($data['mobile']);
			if (empty($data['mobile'])) {
            $this->baoError('请您手机号码');
     	    }
			if (!isPhone($data['mobile']) && !isMobile($data['mobile'])) {
            $this->baoError('联系电话格式不正确');
            }
			$data['content'] = htmlspecialchars($data['content']);//标题名字
			if (empty($data['content'])) {
            $this->baoError('请您填写简介');
     	    }
			if ($words = D('Sensitive')->checkWords($data['content'])) {
				$this->baoError('简介含有敏感词：' . $words);
			}
			$data['audit'] = 1;
        	return $data;
    }
	

	//删除技师
	 public function type_delete($worker_id = 0) {
            $worker_id = (int) $worker_id;
			if(!empty($worker_id)){
				$obj = D('Appointworker');
				if (!$worker = $obj->find($worker_id)) {
					$this->baoError('修改的内容不存在');
				}
				if (!$detail = D('Appoint')->find($worker['appoint_id'])) {
					$this->baoError('请选择要设置的家政');
				}
				if ($detail['closed'] != 0) {
					$this->baoError('该家政已被删除');
				}
				$obj->save(array('worker_id' => $worker_id, 'closed' => 1));
				$this->baoSuccess('操作成功', U('appoint/worker',array('appoint_id'=>$detail['appoint_id'])));
			 }else{
				$this->baoError('操作错误');	
			}
           
       
	 }
	
	//家政订单列表
    public function order(){
		$Appoint = D('Appoint');
        $Appointorder = D('Appointorder');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id,'closed'=>0);
		$st = (int) $this->_param('st');
		if ($st == 1) {
			$map['status'] = 1;
		}elseif ($st == 2) {
			$map['status'] = 2;
		}elseif ($st == 3) {
			$map['status'] = 3;
		}elseif ($st == 4) {
			$map['status'] = 4;
		}elseif ($st == 8) {
			$map['status'] = 8;
		}else{
			$map['status'] = 0;
		}
		$this->assign('st', $st);
		
		$keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['order_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }  
		
        $count = $Appointorder->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $list = $Appointorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $appoint_ids = array();
        foreach ($list as $k => $val) {
            $appoint_ids[$val['appoint_id']] = $val['appoint_id'];
        }
        $this->assign('appoints', $Appoint->itemsByIds($appoint_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    
  
  
	
   //管理员取消订单
    public function cancel($order_id = 0){
        if (is_numeric($order_id) && ($order_id = (int) $order_id)) {
            $obj = D('Appointorder');
            if (!($detial = $obj->find($order_id))) {
                $this->baoError('该订单不存在');
            }elseif(false == D('Appointorder')->Appoint_order_Distribution($order_id,$type =0)){
				$this->baoError('检测到家政配送状态有误');
			}elseif($appoint_order['status'] != 0 ||$appoint_order['status'] != 4){
				$this->baoError('该订单暂时不能取消');
			}elseif($detail['shop_id'] != $this->shop_id){
				$this->baoError('请不要操作他人的订单');
			}else{
				if ($obj->save(array('order_id' => $order_id, 'closed' => 1))) {
					$this->baoSuccess('您已成功删除家政订单', U('appoint/index', array('st' => 1)));
				}else{
					$this->baoError('操作失败');
				}
			}
		}
    }

	//接单
    public function confirm(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Appointorder = D('Appointorder');
        $appoint_order = $Appointorder->where('order_id =' . $order_id)->find();
		if (!($detial = $Appointorder->find($order_id))) {
                $this->baoError('该订单不存在');
        }elseif($appoint_order['status'] != 1){
				$this->baoError('订单状态不正确，但是无法发货');
		}elseif($detial['shop_id'] != $this->shop_id){
				$this->baoError('请不要操作其他商铺的订单');
		}else{
			if ($Appointorder->save(array('order_id' => $order_id, 'status' => 2))) {
				D('Weixintmpl')->weixin_shop_delivery_user($order_id,$this->uid,2);//发货通知买家接口，1外卖，2商城，3家政
				$this->baoSuccess('您已成功接单', U('appoint/index', array('st' => 2)));
			}else{
				$this->baoError('操作失败');
			}
		}
		
    }
	
	
				
				
	//同意退款操作
    public function agree_refund(){
        $order_id = I('order_id', 0, 'trim,intval');
        $Appointorder = D('Appointorder');
        $appoint_order = $Appointorder->where('order_id =' . $order_id)->find();
		if (!($detial = $Appointorder->find($order_id))) {
                $this->baoError('该订单不存在');
        }elseif($appoint_order['status'] != 3){
				$this->baoError('订单状态不正确，无法退款');
		}elseif($detial['shop_id'] != $this->shop_id){
				$this->baoError('请不要操作其他商铺的订单');
		}else{
			if (false == $Appointorder->refund_user($order_id)) {//退款操作
				$this->baoError('非法操作');
			}else{
				$this->baoSuccess('已成功退款',U('appoint/index', array('st' => 4)));	
			}
		}
    }
	
    
}