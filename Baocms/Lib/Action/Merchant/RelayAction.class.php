<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */
class RelayAction extends CommonAction {
	private $create_fields = array('help_id','shop_id','keyword','title','intro','photo','relay_num','stime','ltime','time','use_tips','end_tips','predict_num','max_gold','min_gold','max_num','follower_condtion','member_condtion','collect_count','views','end_photo','clientip','dateline');
	private $edit_fields = array('help_id','shop_id','keyword','title','intro','photo','relay_num','stime','ltime','time','use_tips','end_tips','predict_num','max_gold','min_gold','max_num','follower_condtion','member_condtion','collect_count','views','end_photo','clientip','dateline');
    private $goodscreate_fields = array('id','help_id','title','name','num','sort','photo','shop_id');
	private $goodsedit_fields = array('id','help_id','title','name','num','sort','photo','shop_id');
	public function _initialize() {
        parent::_initialize();
        //$this->assign('types', D('Award')->getCfg());
    }
	public function index($page=1)
    {
        if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		import('ORG.Util.Page'); // 导入分页类 
		$map = array('shop_id' => $shop_id);
		$obj = D('Weixin_relay');
		$pager = array();
		$count = $obj->where($map)->count();
		$Page = new Page($count, 25);
		$show = $Page->show();
		$list = $obj->where($map)->order(array('relay_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $url = U('weixin/relay/preview', array('relay_id' => $val['relay_id']));
            $url = __HOST__ . $url;
            $tooken = 'relay_' . $val['relay_id'];
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
			$obj = D('Weixin_relay');
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
				$keyword['plugin'] = 'relay:'.$relay_id;
				$keyword['type'] = news;
				$keyword['url'] = $data[''];
				$keyword['photo'] = $data['end_photo'];
				if(!$keyword_id = $objk->add($keyword)){
					$this->baoError('添加关键字失败！');
				}	
			}				
			if ($id = $obj->add($data)) {
				 $this->baoSuccess('添加成功', U('relay/index'));
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
        if (empty($data['title'])) {
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
        }
        $data['shop_id'] = $this->shop_id;
        $data['type'] = news;
		$data['dateline'] = NOW_TIME;
        if (empty($data['type'])) {
            $data['type'] = news;
        }
        //$data['create_time'] = NOW_TIME;
        //$data['create_ip'] = get_client_ip();
        return $data;
    }
	public function edit($relay_id = null) {
        if ($relay_id = (int) $relay_id) {
            $obj = D('Weixin_relay');
            if (!$detail = $obj->find($relay_id)) {
                $this->baoError('请选择要编辑的活动');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('不可操作其他商家的活动！');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
				$obj = D('Weixin_relay');
				$data['relay_id'] = $relay_id;			
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('修改成功', U('relay/index'));
                }
                $this->baoError('修改失败');
            } else {
				$this->assign('relay_id', $relay_id);
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
		if (empty($data['title'])) {
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
        }
        return $data;
    }
	public function delete($relay_id=null)
    {
		$obj = D('Weixin_relay');
        if($relay_id = (int)$relay_id){
			if(!$detail = $obj->find($relay_id)){
				$this->baoError('你要删除的内容不存在');
			}elseif($obj->delete($relay_id)){
				$this->baoSuccess('删除成功！',U('relay/index'));
			}
        }else{
			$this->baoError('非法操作！');
		}
    }
	
	public function sn($relay_id = 0) {
        if ($relay_id = (int) $relay_id) {
            $obj = D('Weixin_relaysn');
			$obje = D('Weixin_relay');
			if(!$detail = $obje->find($relay_id)){
				$this->baoError('该活动不存在');
			}else{
				$this->assign('detail', $detail);
			}
			$map = array();
			$map['relay_id'] =$relay_id;
			import('ORG.Util.Page'); // 导入分页类 
			$count = $obj->where($map)->count();
			$Page = new Page($count, 15);
			$show = $Page->show();
			$list = $obj->where($map)->order(array('sn_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
			if($list){
				$uids = '';
				foreach($list as $k => $v){
					$uids[$v['uid']] = $v['uid'];
				}
				$data = array();
				$data['user_id']=array('in',$uids);
				$member_list = D('User')->where($data)->select();
				$this->assign('member_list', $member_list);
				$this->assign('list', $list);
				$this->assign('page', $show); // 赋值分页输出
			}
			 
		}else{
			$this->baoError('该活动不存在');
		}
		$this->display();
    }
	public function sndelete($sn_id=null)
    {
		$weixin = $this->ucenter_weixin();
        if($sn_id = (int)$sn_id){
            if(!$detail = K::M('weixin/relaysn')->detail($sn_id)){
                $this->err->add('你要删除的内容不存在或已经删除', 211);
            }else{
                if(K::M('weixin/relaysn')->delete($sn_id)){
                    $this->err->add('删除内容成功');
                }
            }
        }else if($ids = $this->GP('sn_id')){
            if(K::M('weixin/relaysn')->delete($ids)){
                $this->err->add('批量删除内容成功');
            }
        }else{
            $this->err->add('未指定要删除的内容ID', 401);
        }
    }  

	public function snedit($sn_id=null)
    {
		$obj = D('Weixin_relaysn');
        if($sn_id = (int)$sn_id){
			if(!$detail = $obj->find($sn_id)){
				$this->baoError('你要修改的内容不存在或已经删除');
			}else{
				if($detail['is_use'] == '1'){
					$data['is_use'] = 0;
					$data['use_time'] = '';
				}else{
					$data['is_use'] = 1;
					$data['use_time'] = __TIME;
				}
				$data['sn_id'] = $sn_id;
                if($obj->save($data)){
					$this->baoSuccess('修改成功！',U('relay/sn',array('relay_id'=>$detail['relay_id'])));
                }
            }
        }
    }
	
	public function goods($relay_id)
	{
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
        $obj = D('Weixin_relay');
        $objp = D('Weixin_relayprize'); 
        
        if(!($relay_id = (int)$relay_id)){
            $this->baoError('没有指定摇一摇ID');
        }else if(!$detail = $obj->find($relay_id)){
            $this->baoError('该摇一摇不存在或已经删除');
        }else{
            import('ORG.Util.Page'); // 导入分页类 
            $map = array('shop_id' => $shop_id);
			$map['relay_id'] = $relay_id;
            $count = $objp->where($map)->count();
            $Page = new Page($count, 25);
            $show = $Page->show();
			
            $list = $objp->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
            if($list){
                $map = array();
                $uids = '';
                $obju =  D('User');
                foreach($list as $k => $v){
					$uids[$v['uid']] = $v['uid'];
				}
                 $map['user_id']=array('in',$uids);
			     $member_list = $obju->where($map)->select();
            }
        }
		$this->assign('relay_id', $relay_id); // 赋值数据集
		$this->assign('member_list', $member_list); // 赋值数据集 
		$this->assign('detail', $detail); // 赋值数据集
		$this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

	public function goodscreate($relay_id=null)
    {
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		$obj = D('Weixin_relay');
        if(!($relay_id = (int)$relay_id)){
            $this->baoError('没有指定刮刮卡ID');
        }else if(!$detail = $obj->find($relay_id)){
            $this->baoError('未指定内容ID');
        }if ($this->isPost()) {
			$data = $this->goodscreateCheck($relay_id);
			$objp = D('Weixin_relayprize');
			if ($id = $objp->add($data)) {
				$this->baoSuccess('添加成功', U('relay/goods',array('relay_id'=>$relay_id)));
            }else{
				$this->baoError('添加失败！');
			}	
		}else{
			$this->assign('relay_id', $relay_id); // 赋值数据集
			$this->display();
		}	
    }
	private function goodscreateCheck($relay_id) {
        $data = $this->checkFields($this->_post('data', false), $this->goodscreate_fields);
        $data['title'] = htmlspecialchars($data['title']);
		//$data['shop_ip'] = $shop_id;
		$data['relay_id'] = $relay_id;
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
        }
        $data['shop_id'] = $this->shop_id;
        $data['type'] = news;
		$data['dateline'] = NOW_TIME;
        if (empty($data['type'])) {
            $data['type'] = news;
        }*/
		$data['shop_id'] = $this->shop_id;
        return $data;
    }

	public function goodsedit($id=null)
    {
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		$obj = D('Weixin_relay');
		$objp = D('Weixin_relayprize');
        if (!$detail = $objp->find($id)) {
              $this->baoError('未指定要修改的内容ID');
        }
        if($detail['shop_id'] != $this->shop_id){
            $this->error('不可操作其他商家的活动！');
        }
        if(!$detail['relay_id']){
            $this->baoError('没有指定刮刮卡ID');
        }else if(!$details = $obj->find($relay_id)){
            $this->baoError('您要修改的内容不存在或已经删除');
        }if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), $this->goodsedit_fields);
			$data['id'] = $id;
			if (false !== $objp->save($data)) {
				$this->baoSuccess('修改成功', U('relay/goods',array('relay_id'=>$detail['relay_id'])));
            }else{
				$this->baoError('修改失败失败！');
			}	
		}else{
			$this->assign('detail', $detail); // 赋值数据集
			$this->display();
		}
        
    }
	public function goodsdelete($id=null)
    {
        if($id = (int)$id){
            $objp = D('Weixin_relayprize');
            if(!$detail = $objp->find($id)){
                $this->baoError('你要删除的内容不存在或已经删除');
            }elseif($objp->delete($id)){
                $this->baoSuccess('删除成功！',U('relay/goods',array('relay_id'=>$detail['relay_id'])));
            }else{
                $this->baoError('删除失败！');
            }
        }else{
            $this->baoError('没有指定ID');
        }
    }


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
    }
	
	public function snlist($sn_id){
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		$objU = D('User_weixin');
		$user = $objU->where(array('shop_id'=>$shop_id))->select();
		foreach($user as $k => $v){
					$user=$v;
				}
		$openid = $user['openid'];
		if(!$sn_id){
			$this->baoError('你要查看的内容不存在或已经删除');
		}elseif(!$detail = D('Weixin_relaysn')->find($sn_id)){
			$this->baoError('你要查看的内容不存在或已经删除');
		}else{
			$objl = D('Weixin_relaylist');
			import('ORG.Util.Page'); // 导入分页类 
            $map = array('relay_id' => $relay_id);
			$map = array('openid' => $openid);
            $count = $objl->where($map)->count();
            $Page = new Page($count, 25);
            $show = $Page->show();
            $list = $objl->where($map)->order(array('list_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $this->assign('detail',$detail);
			$this->assign('page', $show);
			$this->assign('list', $list);		
		}
		$this->display();
	}
}