<?php
//WAP会员中心众筹
class CrowdAction extends CommonAction {
	 private $create_fields = array('city_id', 'area_id','title','intro', 'user_id', 'photo', 'cate_id', 'price', 'all_price', 'commission',  'orderby', 'views',  'details','lat','lng', 'end_date','ltime');
    private $edit_fields = array('city_id', 'area_id','title','intro', 'user_id', 'photo', 'cate_id', 'price', 'all_price', 'commission',  'orderby', 'views',  'details', 'lat','lng', 'end_date','ltime');

    public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['crowd'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $this->cates = D('Crowdcate')->fetchAll();
        $this->assign('cates', $this->cates);
		$goods_id = (int) $this->_param('goods_id');
		$this->assign('goods_id', $goods_id);
    }
	
   //我的列表订单
    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
        // 输出模板
    }
   public function loaddata() {
		$Crowdorder = D('Crowdorder');
		import('ORG.Util.Page');
		$map = array('user_id' => $this->uid,'closed'=>0);
        $aready = (int) $this->_param('aready');
        if ($aready == 1) {
            $map['status'] = 0;//未付款
        }elseif ($aready == 2) {
            $map['status'] = 1;//已付款
        }elseif ($aready == 7) {
			$map['status'] = array('IN', array(0,1,2,3,8));//已验证
			$map['is_check'] = 1;//已验证
        }elseif ($aready == 8) {
            $map['status'] = $aready;
        }
		$count = $Crowdorder->where($map)->count(); 
        $Page = new Page($count, 20); 
        $show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
		$list = $Crowdorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach($list as $k => $v){
			$goods_ids[$v['goods_id']] = $v['goods_id'];
			$type_ids[$v['type_id']] = $v['type_id'];
			$address_ids[$v['address_id']] = $v['address_id'];
			$user_ids[$v['user_id']] = $v['user_id'];
			$order_ids[$v['order_id']] = $v['order_id'];
		}
		if (!empty($type_ids)) {
			$this->assign('type', D('Crowdtype')->itemsByIds($type_ids));
		}
		if (!empty($goods_ids)) {
			$this->assign('crowd', D('Crowd')->itemsByIds($goods_ids));
		}
		if (!empty($address_ids)) {
			$this->assign('paddress', D('Paddress')->itemsByIds($address_ids));
		}
		if (!empty($user_ids)) {
			$this->assign('users', D('Users')->itemsByIds($user_ids));
		}
		if (!empty($order_ids)) {
            $Crowdlist = D('Crowdlist')->where(array('order_id' => array('IN', $order_ids)))->select();
            $this->assign('crowdlist', $Crowdlist);
        }
		$this->assign('list', $list); 
        $this->assign('page', $show); 
		$this->display();
    }
	public function detail($order_id){
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Crowdorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error('请不要操作他人的订单');
        }
		$Crowd = D('Crowd')->find($detail['goods_id']);
        $this->assign('crowd', $Crowd);
        $Crowdlist = D('Crowdlist')->find($order_id);
        $this->assign('crowdlist', $Crowdlist);
        $this->assign('paddress', D('Paddress')->find($detail['address_id']));
		$this->assign('crowd_time', $crowd_time = D('Crowd')->crowd_time($Crowd['ltime']));//获取天数
        $this->assign('detail', $detail);
        $this->display();
    }
	//众筹二维码
	public function qrcode(){
        $order_id = $this->_get('order_id');
        if (!($detail = D('Crowdorder')->find($order_id))) {
            $this->error('没有该众筹');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("众筹不存在！");
        }
        if ($detail['status'] == 0 || $detail['is_check'] != 0) {
            $this->error('状态不正确');
        }
        $url = U('user/crowd/check', array('order_id' => $order_id, 't' => NOW_TIME, 'sign' => md5($order_id . C('AUTH_KEY') . NOW_TIME)));
        $token = 'crowdorder_' . $order_id;
        $file = baoQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('detail', $detail);
        $this->display();
    }
	//核销二维码
	public function check(){
        if (empty($this->uid)) {
            $this->error('登录状态失效!', U('Wap/passport/login'));
        }
        $order_id = (int) $this->_param('order_id');
        $obj = D('Crowdorder');
		$ip = get_client_ip();
        $data = $obj->find($order_id);
        if (empty($data)) {
            $this->error('没有找到对应的众筹券信息！', U('index/index'));
        }
        if ($data['uid'] != $this->uid) {
            $this->error('您无权管理该众筹！', U('index/index'));
        }
        if ((int) $data['is_check'] == 0 && (int) $data['status'] == 1) {
            if ($obj->save(array('order_id' => $data['order_id'], 'is_check' => 1, 'check_time' => NOW_TIME,'check_ip' => $ip))) {
                if (!empty($data['price'])) {
                    //D('Users')->addMoney($data['uid'], $data['price'], '众筹结算：订单号：' . $data['order_id']);//这里应该最后一笔统计，暂时不管
                    $this->success('众筹券' . $order_id . '验证成功！', U('index/index'));
                } else {
                     $this->error('验证失败', U('index/index'));
                }
            }
        } else {
            $this->error('该众筹券无效或已经使用', U('index/index'));
        }
    }
	
	 public function crowd_list(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
    //我发布的众筹列表
    public function crowd_listdata() {
        $Crowd = D('Crowd');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'user_id' => $this->uid);
        $aready = (int) $this->_param('aready');
        if ($aready == 1) {
            $map['closed'] = 0;
        }elseif ($aready == 2) {
            $map['closed'] = 1;//往期
        }
        $count = $Crowd->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Crowd->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $v) {
			$user_ids[$v['user_id']] = $v['uid'];
			if ($v['ltime']) {
				$crowd_time = $Crowd->crowd_time($v['ltime']);
            }
			$list[$k]['crowd_time'] = $crowd_time;
        }
		if (!empty($user_ids)) {
			$this->assign('users', D('Users')->itemsByIds($user_ids));
		}
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
   //往期众筹
    public function history() {
        $Goods = D('Goods');
		$Crowd = D('Goodscrowd');
        import('ORG.Util.Page');
		$map = array('closed' => 0, 'shop_id' => $this->shop_id,'type'=>'crowd','ltime'=>array('LT',time()));
        $count = $Goods->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show();
        $list = $Goods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
			$goods_ids[] = $val['goods_id']; 
            $val = $Goods->_format($val);
            $list[$k] = $val;
        }
		if($goods_ids){
			$f['goods_id'] = array('IN',implode(',',$goods_ids));
			$Crowd_list = $Crowd->where($f)->select();
			foreach($Crowd_list as $k => $v){
				$Crowd_lists[$v['goods_id']] = $v;
			}
			$this->assign('crowd', $Crowd_lists);
		}
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
    //添加众筹
	public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Crowd');
            if ($goods_id = $obj->add($data)) {
                $photos = $this->_post('photos', false);
                if (!empty($photos)) {
                    D('Crowdphoto')->upload($goods_id, $photos);
                }
                $this->fengmiMsg('恭喜你添加众筹成功', U('crowd/crowd_list'));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->display();
        }
    }


    //众筹分类
    public function child($parent_id = 0){
        $datas = D('Crowdcate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['cate_id']) {
                                $str .= '<option value="' . $var3['cate_id'] . '">  --' . $var3['cate_name'] . '</option>' . "\n\r";
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->fengmiMsg('城市不能为空');
        }
		
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->fengmiMsg('地区不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg('产品名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['title'])) {
            $this->fengmiMsg('描述不能为空');
        }
        $data['user_id'] = $this->uid;
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('请选择分类');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('缩略图格式不正确');
        }
		$data['all_price'] = (int) ($data['all_price'] * 100);
        if (empty($data['all_price'])) {
           $this->fengmiMsg('众筹金额不能为空'); 
		}
        $data['commission'] = (int) ($data['commission'] * 100);
        if ($data['commission'] < 0) {
            $this->fengmiMsg('佣金不能为负数');
        }
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->fengmiMsg('众筹详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('众筹详情含有敏感词：' . $words);
        } $data['ltime'] = htmlspecialchars($data['ltime']);
        if (empty($data['ltime'])) {
            $this->fengmiMsg('过期时间不能为空');
        }
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    //编辑众筹
    public function edit($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
            $obj = D('Crowd');
            if (!$detail = $obj->find($goods_id)) {
                $this->fengmiMsg('请选择要编辑的众筹');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['goods_id'] = $goods_id;
                if (false !== $obj->save($data)) {
					$photos = $this->_post('photos', false);
                    if (!empty($photos)) {
                        D('Crowdphoto')->upload($goods_id, $photos);
                    }
                    $this->fengmiMsg('恭喜您编辑众筹成功', U('crowd/crowd_list'));
                }
                $this->fengmiMsg('操作失败');
            } else {
                $this->assign('detail', $obj->_format($detail));
				$this->assign('photos', D('Crowdphoto')->getPics($goods_id));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
				$this->assign('parent_id',D('Crowdcate')->getParentsId($detail['cate_id']));
                $this->display();
            }
        } else {
            $this->fengmiMsg('请选择要编辑的众筹');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->fengmiMsg('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->fengmiMsg('地区不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg('产品名称不能为空');
        }
		$data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['title'])) {
            $this->fengmiMsg('描述不能为空');
        }
        $data['user_id'] = $this->uid;
        $shop = D('Users')->find($data['user_id']);
        if (empty($shop)) {
            $this->fengmiMsg('请选择正确的会员');
        }
   
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('请选择分类');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('缩略图格式不正确');
        } 
		$data['all_price'] = (int) ($data['all_price'] * 100);
        if (empty($data['all_price'])) {
           $this->fengmiMsg('众筹金额不能为空'); 
		}
        $data['commission'] = (int) ($data['commission'] * 100);
        if ($data['commission'] < 0) {
            $this->fengmiMsg('佣金不能为负数');
        }
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->fengmiMsg('众筹详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('众筹详情含有敏感词：' . $words);
        } 
		$data['ltime'] = htmlspecialchars($data['ltime']);
        if (empty($data['ltime'])) {
            $this->fengmiMsg('过期时间不能为空');
        }
		$data['lng'] = htmlspecialchars(trim($data['lng']));
        $data['lat'] = htmlspecialchars(trim($data['lat']));
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		$data['audit'] = 0;
        return $data;
    }


	public function setting($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
			$detail = $Crowd->find($goods_id);
			if ($detail['user_id'] != $this->uid) {
                $this->error('请不要操作别人的众筹');
            }
			$this->assign('detail', $detail);
			$this->assign('meals', $Crowdtype->where(array('goods_id' => $goods_id))->select());
			$this->display();
        } else {
            $this->error('请选择要设置的众筹');
        }
    }

	public function type_create($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
            if (!$detail = $Crowd->find($goods_id)) {
                $this->error('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->error('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->error('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);

			if ($data = $this->_post('data', false)) {
				if (!empty($data['price']) && !empty($data['content']) && !empty($data['max_num']) && !empty($data['fahuo'])){
					$Crowdtype->add(array(
						'goods_id' => $goods_id,
						'price' => $data['price']*100,
						'content' => $data['content'],
						'max_num' => $data['max_num'],
						'yunfei' => $data['yunfei']*100,
						'fahuo' => $data['fahuo'],
						'choujiang' => $data['choujiang'],
						'img' => $data['img'],
						'dateline' => time(),
					));
					$this->fengmiMsg('操作成功', U('crowd/setting',array('goods_id'=>$detail['goods_id'])));
				}else{
					 $this->fengmiMsg('内容不能为空');
				}
			}else{
				$this->assign('detail', $detail);
				$this->display();
			}

		} else {
            $this->error('请选择要设置的众筹');
        }
	}

	public function type_edit($type_id)
	{
		if ($type_id = (int) $type_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
			if (!$type = $Crowdtype->find($type_id)) {
                $this->fengmiMsg('修改的内容不存在');
            }
			$goods_id = $type['goods_id'];
            if (!$detail = $Crowd->find($goods_id)) {
                $this->fengmiMsg('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->fengmiMsg('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->fengmiMsg('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);			
			if ($data = $this->_post('data', false)) {
				if (!empty($data['price']) && !empty($data['content']) && !empty($data['max_num']) && !empty($data['fahuo'])){
					$data = array(
						'goods_id' => $goods_id,
						'price' => $data['price']*100,
						'content' => $data['content'],
						'max_num' => $data['max_num'],
						'yunfei' => $data['yunfei']*100,
						'fahuo' => $data['fahuo'],
						'choujiang' => $data['choujiang'],
						'img' => $data['img'],
					);

					if (false !== $Crowdtype->where(array('type_id' => $type_id))->save($data)) {
						$this->fengmiMsg('操作成功', U('crowd/setting',array('goods_id'=>$detail['goods_id'])));
					}
					
				}else{
					 $this->fengmiMsg('内容不能为空');
				}
			}else{
				$this->assign('type', $type);
				$this->assign('detail', $detail);
				$this->display();
			}

		} else {
            $this->error('修改的内容不存在');
        }
	}

	public function type_delete($type_id)
	{
		if ($type_id = (int) $type_id) {
			$Crowd = D('Crowd');
            $Crowdtype = D('Crowdtype');
			if (!$type = $Crowdtype->find($type_id)) {
                $this->fengmiMsg('删除的内容不存在');
            }
			$goods_id = $type['goods_id'];
            if (!$detail = $Crowd->find($goods_id)) {
                $this->fengmiMsg('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->fengmiMsg('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->fengmiMsg('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);
			
			if (false !== $Crowdtype->where(array('type_id' => $type_id))->delete()) {
				$this->fengmiMsg('操作成功', U('crowd/setting',array('goods_id'=>$detail['goods_id'])));
			}
		} else {
            $this->fengmiMsg('删除的内容不存在');
        }
	}

	public function project($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdproject = D('Crowdproject');
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			$this->assign('meals', $Crowdproject->where(array('goods_id' => $goods_id))->select());
			$this->display();
        } else {
            $this->error('请选择要设置的众筹');
        }
	}

	public function project_create($goods_id){
		if ($goods_id = (int) $goods_id) {
			$Crowd = D('Crowd');
            $Crowdproject = D('Crowdproject');

            if (!$detail = $Crowd ->find($goods_id)) {
                $this->error('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->error('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->error('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);
			
			
			if ($data = $this->_post('data', false)) {
				if (!empty($data['content'])){
					$Crowdproject->add(array(
						'goods_id' => $goods_id,
						'content' => $data['content'],
						'dateline' => time(),
					));
					$this->fengmiMsg('操作成功', U('crowd/project',array('goods_id'=>$detail['goods_id'])));
				}else{
					 $this->fengmiMsg('内容不能为空');
				}
			}else{
				$this->assign('detail', $detail);
				$this->display();
			}
		} else {
            $this->error('请选择要设置的众筹');
        }
	}

	public function project_delete($project_id){
		if ($project_id = (int) $project_id) {
			$Crowd = D('Crowd');
            $Crowdproject = D('Crowdproject');
			if (!$type = $Crowdproject->find($project_id)) {
                $this->fengmiMsg('删除的内容不存在');
            }
			$goods_id = $type['goods_id'];
            if (!$detail = $Crowd->find($goods_id)) {
                $this->fengmiMsg('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->fengmiMsg('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->fengmiMsg('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);
			
			if (false !== $Crowdproject->where(array('project_id' => $project_id))->delete()) {
				$this->fengmiMsg('操作成功', U('crowd/project',array('goods_id'=>$detail['goods_id'])));
			}
		} else {
            $this->fengmiMsg('删除的内容不存在');
        }
	}
 	public function ask($goods_id){
		$goods_id = (int) $goods_id;
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
		$this->assign('goods_id', $goods_id); 
        $this->display();
    }
	public function askdata($goods_id){
		if ($goods_id = (int)$this->_param('goods_id')) {
			import('ORG.Util.Page');
			$Crowd = D('Crowd');
            $Crowdask = D('Crowdask');
			$map = array();
			$count = $Crowd->where($map)->count(); 
			$Page = new Page($count, 25); 
			$show = $Page->show();
			$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
			$p = $_GET[$var];
			if ($Page->totalPages < $p) {
				die('0');
			}
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			$ask = $Crowdask->where(array('goods_id' => $goods_id))->select();
			$this->assign('meals', $ask);
			foreach($ask as $k => $v){
				$user_ids[$v['uid']] = $v['uid'];
			}
			if (!empty($user_ids)) {
				$this->assign('users', D('Users')->itemsByIds($user_ids));
			}
			$this->assign('page', $show); 
			$this->display();
        } else {
            $this->error('请选择众筹');
        }
	}

	public function ask_answer($ask_id){
		if ($ask_id = (int) $ask_id) {
			$Crowd = D('Crowd');
            $Crowdask = D('Crowdask');
			if (!$ask = $Crowdask->find($ask_id)) {
                $this->error('该问题不存在');
            }
            if (!$detail = $Crowd ->find($ask['goods_id'])) {
                $this->error('请选择要设置的众筹');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->error('请不要操作别人的众筹');
            }
            if ($detail['closed'] != 0) {
                $this->error('该众筹已被删除');
            }
            $Crowd_list = $Crowd->find($goods_id);
			
			if ($data = $this->_post('data', false)) {
				if (!empty($data['answer_c'])){
					$Crowdask->where(array('ask_id' => $ask_id))->save(array(
						'answer_c' => $data['answer_c'],
						'answer_time' => time(),
					));
					$this->fengmiMsg('操作成功', U('crowd/ask',array('goods_id'=>$ask['goods_id'])));
				}else{
					 $this->fengmiMsg('内容不能为空');
				}
			}else{
				$this->assign('detail', $detail);
				$this->assign('ask', $ask);
				$this->display();
			}
		} else {
            $this->error('请选择要设置的众筹');
        }
	}


	public function follow($goods_id){
		$goods_id = (int) $goods_id;
		$this->assign('goods_id', $goods_id); 
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
	public function followdata($goods_id,$aready){
		if ($goods_id = (int) $goods_id) {
			$aready = (int) $this->_param('aready');
			import('ORG.Util.Page');
			$Crowd = D('Crowd');
            $Crowdfollow = D('Crowdfollow');
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			
			$map = array('goods_id' => $goods_id);
			if ($aready == 1) {
				$map['type'] = follow;
			}elseif ($aready == 2) {
				$map['type'] = zan;
			}
			
			$follow = $Crowdfollow->where($map)->select();
			$this->assign('meals', $follow);

			foreach($follow as $k => $v){
				$user_ids[$v['uid']] = $v['uid'];
			}
			if (!empty($user_ids)) {
				$this->assign('users', D('Users')->itemsByIds($user_ids));
			}
			$this->assign('page', $show); 
			$this->display();
        } else {
            $this->error('请选择众筹');
        }
	}

	public function lists($goods_id){
		$goods_id = (int) $goods_id;
		$this->assign('goods_id', $goods_id); 
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
	
	
	public function listsdata($goods_id,$aready){
		if ($goods_id = (int) $goods_id) {
			$aready = (int) $this->_param('aready');
			import('ORG.Util.Page');
			$Crowd = D('Crowd');
            $Crowdlist = D('Crowdlist');
			$detail = $Crowd->find($goods_id);
			$this->assign('detail', $detail);
			$map = array('goods_id' => $goods_id);
			if ($aready == 1) {
				$map['is_zhong'] = 0;
			}elseif ($aready == 2) {
				$map['is_zhong'] = 1;
			}
			$count = $Crowdlist->where($map)->count(); 
			$Page = new Page($count, 25); 
			$show = $Page->show();
			$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
			$p = $_GET[$var];
			if ($Page->totalPages < $p) {
				die('0');
			}
			$list = $Crowdlist->where($map)->select();
			$this->assign('meals', $list);
			foreach($list as $k => $v){
				$user_ids[$v['uid']] = $v['uid'];
				$type_ids[$v['type_id']] = $v['type_id'];
			}
			if (!empty($type_ids)) {
				$this->assign('type', D('Crowdtype')->itemsByIds($type_ids));
			}
			if (!empty($user_ids)) {
				$this->assign('users', D('Users')->itemsByIds($user_ids));
			}
			$this->assign('page', $show); 
			$this->display();
        } else {
            $this->fengmiMsg('请选择众筹');
        }
	}
   //中奖状态
	public function zhong($list_id){
		if(!empty($list_id)){
			$Crowdlist = D('Crowdlist');
			$data['is_zhong'] = '1';
			$list = $Crowdlist->where(array('list_id'=>$list_id))->find();
			if($Crowdlist->where(array('list_id'=>$list_id))->save($data)){
				$this->fengmiMsg('设为中奖成功', U('crowd/lists',array('goods_id'=>$list['goods_id'])));
			}
		}else{
			$this->fengmiMsg('错误');		
		}
		
	}

    //验证众筹劵
    public function used() {
        if ($this->isPost()) {
			if (empty($this->uid)) {
            	$this->fengmiMsg('登录状态失效!', U('Wap/passport/login'));
       		}
            $code = $this->_post('code', false);
            if (empty($code)) {
                $this->fengmiMsg('请输入众筹券！');
            }
            $obj = D('Crowdorder');
            $return = array();
            $ip = get_client_ip();
			$crowd_list_jump_url =  U('crowd/crowd_list',array('aready'=>1));
            if (count($code) > 10) {
                $this->fengmiMsg('一次最多验证10条众筹券！');
            }
            foreach ($code as $key => $var) {
                $var = trim(htmlspecialchars($var));
                if (!empty($var)) {
                    $data = $obj->find(array('where' => array('code' => $var)));
                    if (!empty($data) && $data['uid'] == $this->uid && (int) $data['is_check'] == 0 && (int) $data['status'] == 1) {
                        if ($obj->save(array('order_id' => $data['order_id'], 'is_check' => 1))) {        
							if (!empty($data['price'])) {
								//这里暂时没增加逻辑
								$this->fengmiMsg('众筹券' . $order_id . '验证成功！', $crowd_list_jump_url);
							} else {
								 $this->fengmiMsg('验证失败', $crowd_list_jump_url);
							}
                        }
                    } else {
                        $this->fengmiMsg('该众筹券无效或已经使用', $crowd_list_jump_url);
                    }
                }
            }
        } else {
            $this->display();
        }
    }

}
