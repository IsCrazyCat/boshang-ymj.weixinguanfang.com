<?php
/**
 * Copy Right IJH.CC
 * Each engineer has a duty to keep the code elegant
 * $Id$
 */

class HelpAction extends CommonAction {

	private $create_fields = array('help_id','shop_id','keyword','title','intro','photo','stime','ltime','use_tips','end_tips','predict_num','max_num','follower_condtion','member_condtion','collect_count','views','end_photo','clientip','dateline');
	private $edit_fields = array('help_id','shop_id','keyword','title','intro','photo','stime','ltime','use_tips','end_tips','predict_num','max_num','follower_condtion','member_condtion','collect_count','views','end_photo','clientip','dateline');
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
		$obj = D('Weixin_help');
		$pager = array();
		$count = $obj->where($map)->count();
		$Page = new Page($count, 25);
		$show = $Page->show();
		$list = $obj->where($map)->order(array('help_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $url = U('weixin/help/preview', array('help_id' => $val['help_id']));
            $url = __HOST__ . $url;
            $tooken = 'help_' . $val['help_id'];
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
			$obj = D('Weixin_help');
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
				$keyword['plugin'] = 'help:'.$help_id;
				$keyword['type'] = news;
				$keyword['content'] = $data['use_tips'];
				$keyword['plugin'] = 'help:'.$help_id;
				$keyword['url'] = $data[''];
				$keyword['photo'] = $data['end_photo'];
				if(!$keyword_id = $objk->add($keyword)){
					$this->baoError('添加关键字失败！');
				}	
			}				
			if ($id = $obj->add($data)) {
				 $this->baoSuccess('添加成功', U('help/index'));
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
	
	public function edit($help_id = null) {
        if ($help_id = (int) $help_id) {
            $obj = D('Weixin_help');
            if (!$detail = $obj->find($help_id)) {
                $this->baoError('请选择要编辑的活动');
            }
            if($detail['shop_id'] != $this->shop_id){
                $this->error('不可操作其他商家的活动！');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
				$obj = D('Weixin_help');
				$data['help_id'] = $help_id;			
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('修改成功', U('help/index'));
                }
                $this->baoError('修改失败');
            } else {
				$this->assign('help_id', $help_id);
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
	
	public function delete($help_id=null)
    {
		$obj = D('Weixin_help');
        if($help_id = (int)$help_id){
			if(!$detail = $obj->find($help_id)){
				$this->baoError('你要删除的内容不存在');
			}elseif($obj->delete($help_id)){
				$this->baoSuccess('删除成功！',U('help/index'));
			}
        }else{
			$this->baoError('非法操作！');
		}
    }

	public function sn($help_id = 0) {
        if ($help_id = (int) $help_id) {
            $obj = D('Weixin_helpsn');
			$obje = D('Weixin_help');
			if(!$detail = $obje->find($help_id)){
				$this->baoError('该活动不存在');
			}else{
				$this->assign('detail', $detail);
			}
			$map = array();
			$map['help_id'] =$help_id;
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
		$obj = D('Weixin_help');
        if($sn_id = (int)$sn_id){
			if(!$detail = $obj->find($sn_id)){
				$this->baoError('你要修改的内容不存在或已经删除');
			}elseif($obj->delete($sn_id)){
				$this->baoSuccess('删除成功！',U('help/sn',array('help_id'=>$detail['help_id'])));
			}
        }else{
			$this->baoError('非法操作！');
		}
    }  

	public function snedit($sn_id=null)
    {
		$obj = D('Weixin_helpsn');
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
					$this->baoSuccess('修改成功！',U('help/sn',array('help_id'=>$detail['help_id'])));
                }
            }
        }
    }

	public function goods($help_id)
	{
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
        $obj = D('Weixin_help');
        $objp = D('Weixin_helpprize'); 
        
        if(!($help_id = (int)$help_id)){
            $this->baoError('没有指定摇一摇ID');
        }else if(!$detail = $obj->find($help_id)){
            $this->baoError('该摇一摇不存在或已经删除');
        }else{
            import('ORG.Util.Page'); // 导入分页类
            $map = array('shop_id' => $shop_id);
            $map = array('help_id' => $help_id);
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
		$this->assign('help_id', $help_id); // 赋值数据集
		$this->assign('member_list', $member_list); // 赋值数据集 
		$this->assign('detail', $detail); // 赋值数据集
		$this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }


	public function goodscreate($help_id=null)
    {
		if(!$shop_id = $this->shop_id){
			 $this->baoError('商家不能为空');
		}
		$obj = D('Weixin_help');
        if(!($help_id = (int)$help_id)){
            $this->baoError('没有指定刮刮卡ID');
        }else if(!$detail = $obj->find($help_id)){
            $this->baoError('未指定内容ID');
        }if ($this->isPost()) {
			$data = $this->goodscreateCheck($help_id);
			$objp = D('Weixin_helpprize');
			if ($id = $objp->add($data)) {
				$this->baoSuccess('添加成功', U('help/goods',array('help_id'=>$help_id)));
            }else{
				$this->baoError('添加失败！');
			}	
		}else{
			$this->assign('help_id', $help_id); // 赋值数据集
			$this->display();
		}	
    }

	private function goodscreateCheck($help_id) {
        $data = $this->checkFields($this->_post('data', false), $this->goodscreate_fields);
        $data['title'] = htmlspecialchars($data['title']);
		//$data['shop_ip'] = $shop_id;
		$data['help_id'] = $help_id;
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
		$obj = D('Weixin_help');
		$objp = D('Weixin_helpprize');
        if (!$detail = $objp->find($id)) {
              $this->baoError('未指定要修改的内容ID');
        }
        if($detail['shop_id'] != $this->shop_id){
            $this->error('不可操作其他商家的活动！');
        }
        if(!$detail['help_id']){
            $this->baoError('没有指定刮刮卡ID');
        }else if(!$details = $obj->find($help_id)){
            $this->baoError('您要修改的内容不存在或已经删除');
        }if ($this->isPost()) {
			$data = $this->checkFields($this->_post('data', false), $this->goodsedit_fields);
			$data['id'] = $id;
			if (false !== $objp->save($data)) {
				$this->baoSuccess('修改成功', U('help/goods',array('help_id'=>$detail['help_id'])));
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
            $objp = D('Weixin_helpprize');
            if(!$detail = $objp->find($id)){
                $this->baoError('你要删除的内容不存在或已经删除');
            }elseif($objp->delete($id)){
                $this->baoSuccess('删除成功！',U('help/goods',array('help_id'=>$detail['help_id'])));
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
		}elseif(!$detail = D('Weixin_helpsn')->find($sn_id)){
			$this->baoError('你要查看的内容不存在或已经删除');
		}else{
			$objl = D('Weixin_helplist');
			import('ORG.Util.Page'); // 导入分页类
            $map = array('help_id' => $help_id);
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