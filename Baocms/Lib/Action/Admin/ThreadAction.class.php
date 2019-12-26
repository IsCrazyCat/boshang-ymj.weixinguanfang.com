<?php


class ThreadAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
        $this->assign('cates',D('Threadcate')->fetchAll());
    }
    
    
    public function index() {
        $Thread = D('Thread');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['thread_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Thread->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Thread->where($map)->order(array('thread_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


    //贴吧打赏列表
	public function donate(){
        $Threaddonate = D('Threaddonate');
        import('ORG.Util.Page');
        $map = array();
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = $Threaddonate->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Threaddonate->where($map)->order(array('donate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
			$user_ids[$val['user_id']] = $val['user_id'];
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
		$this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('sum', $sum = $Threaddonate->where($map)->sum('money'));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function order() {
        $Thread = D('Thread');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['thread_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Thread->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Thread->where($map)->order(array('thread_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }
    

    public function create() {
        $obj = D('Thread');
        if ($this->isPost()) {
            $data = $this->createCheck();
            if ($thread_id = $obj->add($data)) {
                $this->baoSuccess('操作成功', U('thread/index'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
       
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), array('thread_name','cate_id','user_id','intro', 'photo','banner','is_hot','is_essence','orderby'));
        $data['thread_name'] = htmlspecialchars($data['thread_name']);
        if (empty($data['thread_name'])) {
            $this->baoError('主题名称不能为空');
        }$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('主题分类不能为空');
        }
		
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
        $Thread = D('Thread')->find(array('where' => array('user_id' => $data['user_id'])));
        if (!empty($shop)) {
           $this->baoError('该管理者已经拥有贴吧');
        }
		   
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } 
        $data['banner'] = htmlspecialchars($data['banner']);
        if (empty($data['banner'])) {
            $this->baoError('请上传banner图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('banner图格式不正确');
        } 
        $data['intro'] = SecurityEditorHtml($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('主题简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('主题简介含有敏感词：' . $words);
        } 
        $data['is_hot'] = (int)$data['is_hot'];
		$data['is_essence'] = (int)$data['is_essence'];
		$data['orderby'] = (int)$data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    
    
    public function edit($thread_id = 0) {

        if ($thread_id = (int) $thread_id) {
            $obj = D('Thread');
            if (!$detail = $obj->find($thread_id)) {
                $this->baoError('请选择要编辑的主题');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['thread_id'] = $thread_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('thread/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的主题');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), array('thread_name','cate_id','user_id','intro', 'photo','banner','is_hot','is_essence','orderby'));
        $data['thread_name'] = htmlspecialchars($data['thread_name']);
        if (empty($data['thread_name'])) {
            $this->baoError('主题名称不能为空');
        }$data['cate_id'] = (int)$data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('主题分类不能为空');
        }
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
        $Thread = D('Thread')->find(array('where' => array('user_id' => $data['user_id'])));
        if (!empty($shop)) {
           $this->baoError('该管理者已经拥有贴吧');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        } 
        $data['banner'] = htmlspecialchars($data['banner']);
        if (empty($data['banner'])) {
            $this->baoError('请上传banner图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('banner图格式不正确');
        } 
        $data['intro'] = SecurityEditorHtml($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('主题简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('主题简介含有敏感词：' . $words);
        } 
        $data['is_hot'] = (int)$data['is_hot'];
		$data['is_essence'] = (int)$data['is_essence'];
		$data['orderby'] = (int)$data['orderby'];
        return $data;
    }
    
    
    public function delete($thread_id = 0) {
        $obj = D('Thread');
        if (is_numeric($thread_id) && ($thread_id = (int) $thread_id)) {
            $obj->save(array('thread_id' => $thread_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('thread/index'));
        } else {
            $thread_id = $this->_post('thread_id', false);
            if (is_array($thread_id)) {
                foreach ($thread_id as $id) {
                    $obj->save(array('thread_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('thread/index'));
            }
            $this->baoError('请选择要删除的主题');
        }
    }
    
}
