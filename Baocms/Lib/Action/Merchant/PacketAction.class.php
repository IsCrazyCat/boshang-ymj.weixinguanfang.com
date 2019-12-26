<?php


class PacketAction extends CommonAction {
    private $create_fields = array('id','shop_id','title','item_min','title','keyword','msg_pic','desc','info','start_time','end_time','ext_total','get_number','value_count','is_open','item_num','item_sum','item_max','item_unit','packet_type','deci','people','password');
    private $edit_fields = array('id','shop_id','title','item_min','title','keyword','msg_pic','desc','info','start_time','end_time','ext_total','get_number','value_count','is_open','item_num','item_sum','item_max','item_unit','packet_type','deci','people','password');
    public function _initialize() {
        parent::_initialize();
    }
    public function index($page=1)
    {
        if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
        import('ORG.Util.Page'); // 导入分页类 
		$map = array('shop_id' => $shop_id);
		$obj = D('Weixin_packet');
		$count = $obj->where($map)->count();
		$Page = new Page($count, 25);
		$show = $Page->show();
		$list = $obj->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $url = U('weixin/packet/index', array('packet_id' => $val['id']));
            $url = __HOST__ . $url;
            $tooken = 'packet_' . $val['id'];
            $file = baoQrCode($tooken, $url);
            $list[$k]['file'] = $file;
        }
		$this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
	
	public function create()
	{
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		if ($this->isPost()) {
			$data = $this->createCheck();
			$obj = D('Weixin_packet');
			$objk = D('Shop_weixin_keyword');
			
			$map = array();
			$map['shop_id'] = $shop_id;
			$map['keyword'] = $data['keyword'];
			if($k = $objk->where($map)->select()){
				$this->baoError('该关键字已经被使用，请修改关键字');
			}else{
				$keyword = array();
				$keyword['shop_id'] = $data['shop_id'];
				$keyword['keyword'] = $data['keyword'];
				$keyword['type'] = news;
				$keyword['keyword'] = $data['keyword'];
				$keyword['content'] = $data['use_tips'];
				$keyword['url'] = $data[''];
				$keyword['photo'] = $data['end_photo'];
				if(!$keyword_id = $objk->add($keyword)){
					$this->baoError('添加关键字失败！');
				}	
			}				
			if ($id = $obj->add($data)) {
				 $this->baoSuccess('添加成功', U('packet/index'));
            }else{
				$this->baoError('添加失败！');
			}	
		}else{
			$this->display();
		}
	}
	
	private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
       /* if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
		if (empty($data['stime'])) {
            $this->baoError('开始时间不能为空');
        }
		if (empty($data['ltime'])) {
            $this->baoError('结束时间不能为空');
        }
		if (empty($data['intro'])) {
            $this->baoError('封面简介不能为空');
        }*/
        $data['shop_id'] = $this->shop_id;
        $data['type'] = news;
		$data['dateline'] = NOW_TIME;
        if (empty($data['type'])) {
            $data['type'] = news;
        }
        $data['create_ip'] = get_client_ip();
        return $data;
    }


	public function edit($id = null) {
        if ($id = (int) $id) {
            $obj = D('Weixin_packet');
            if (!$detail = $obj->find($id)) {
                $this->baoError('请选择要编辑的活动');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('不可操作其他商家的活动！');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
				$obj = D('Weixin_packet');
				$data['id'] = $id;			
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('修改成功', U('packet/index'));
                }
                $this->baoError('修改失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑活动');
        }
    }
	
	private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
		/*if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
		if (empty($data['stime'])) {
            $this->baoError('开始时间不能为空');
        }
		if (empty($data['ltime'])) {
            $this->baoError('结束时间不能为空');
        }
		if (empty($data['intro'])) {
            $this->baoError('封面简介不能为空');
        }*/
        return $data;
    }
	public function delete($id=null)
    {
		$obj = D('Weixin_packet');
        if($id = (int)$id){
			if(!$detail = $obj->find($id)){
				$this->baoError('你要删除的内容不存在');
			}elseif($obj->delete($id)){
				$this->baoSuccess('删除成功！',U('packet/index'));
			}else{
                $this->baoError('删除失败！');
            }
        }else{
            $this->baoError('没有指定ID');
        }
    }


	public function sn($id = 0) {
        if ($packet_id = (int) $id) {
            $obj = D('Weixin_packetsn');
			$obje = D('Weixin_packet');
			if(!$detail = $obje->find($packet_id)){
				$this->baoError('该活动不存在');
			}else{
				$this->assign('detail', $detail);
			}
			$map = array();
			$map['packet_id'] =$packet_id;
			import('ORG.Util.Page'); // 导入分页类 
			$count = $obj->where($map)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$list = $obj->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($list){
                $this->assign('list', $list);
				$this->assign('page', $show); // 赋值分页输出
			}
			 
		}else{
			$this->baoError('该红包不存在');
		}
		$this->display();
    }
	public function sndelete($sn_id=null)
    {
		$obj = D('Weixin_packetsn');
        if($sn_id = (int)$sn_id){
			if(!$detail = $obj->find($sn_id)){
				$this->baoError('你要修改的内容不存在或已经删除');
			}elseif($obj->delete($sn_id)){
				$this->baoSuccess('删除成功！',U('packet/sn',array('id'=>$detail['packet_id'])));
			}
        }
    }

	public function snedit($sn_id=null)
    {
		$obj = D('Weixin_packetsn');
        if($sn_id = (int)$sn_id){
			if(!$detail = $obj->find($sn_id)){
				$this->baoError('你要修改的内容不存在或已经删除');
			}else{
				if($detail['is_use'] == '1'){
					$data['is_use'] = 0;
					$data['use_time'] = '';
				}else{
					$data['is_use'] = 1;
					$data['use_time'] =time();
				}
				$data['id'] = $sn_id;
                if($obj->save($data)){
					$this->baoSuccess('修改成功！',U('packet/sn',array('packet_id'=>$detail['packet_id'])));
                }else{
                    print_r($data);die;

                    $this->baoError($data);
                }
            }
        }else{
			$this->baoError('没有指定红包ID');
		}
    } 

	public function logs($id)
	{
		$obj = D('Weixin_packetsn');
        if($sn_id = (int)$id){
			$data['packet_id'] = $sn_id;
			if(!$detail = $obj->where($data)->select()){
				$this->baoError('你要修改的内容不存在或已经删除');
			}else{
				$objl = D('Weixin_packetling');
				$map['packet_id'] = $sn_id;
				import('ORG.Util.Page'); // 导入分页类 
				$count = $objl->where($map)->count();
				$Page = new Page($count, 15);
				$show = $Page->show();
				$list = $objl->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
                $packet = D('Weixin_packet')->find($detail['packet_id']);
				if($list){
					$this->assign('page', $show); // 赋值分页输出
                    $this->assign('packet', $packet); 
					$this->assign('list', $list); 
				}
			}
		}else{
			$this->baoError('没有指定红包ID');
		}
		$this->assign('detail', $detail); 
		$this->display();
	}
	
	public function logsdelete($id=null)
    {
		$objl = D('Weixin_packetling');
        if($id = (int)$id){
			if(!$detail = $objl->find($id)){
				$this->baoError('你要修改的内容不存在或已经删除');
			}elseif($objl->delete($id)){
				$this->baoSuccess('删除成功！',U('packet/logs',array('id'=>$detail['packet_id'])));
			}
        }else{
			$this->baoError('没有指定ID');
		}
			
	}
	
/*
	protected function wechat_client()
    {
        static $client = null;
        if($client === null){
            if(!$client = K::M('weixin/weixin')->admin_wechat_client()){
                exit('网站公众号设置错误');
            }
        }
        return $client;
    }

    protected function access_openid($force = false)
    {
        static $openid = null;
        if($force || $openid === null){
            if($code = $this->GP('code')){
                $client = $this->wechat_client();
                $ret = $client->getAccessTokenByCode($code);
                $openid = $ret['openid'];
            }else{
                if(!$openid = $this->cookie->get('wx_openid')){
                    $client = $this->wechat_client();
                    $url = $this->request['url'].'/'.$this->request['uri'];
                    $authurl = $client->getOAuthConnectUri($url, $state, 'snsapi_userinfo');
                    header('Location:'.$authurl);
                    exit();
                }
            }
            $this->cookie->set('wx_openid', $openid);
        }
        if(empty($openid)){
            exit('获取授权失败');
        }
        return $openid;
    }*/
}