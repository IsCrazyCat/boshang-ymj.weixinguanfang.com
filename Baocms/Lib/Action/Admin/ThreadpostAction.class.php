<?php


class ThreadpostAction extends CommonAction {
	
	private $create_fields = array('city_id', 'area_id', 'title', 'user_id', 'cate_id', 'cate_id', 'orderby', 'details','is_fine');
    private $edit_fields = array('city_id', 'area_id', 'title', 'user_id','cate_id', 'details', 'orderby', 'is_fine');

    public function _initialize() {
        parent::_initialize();
        $this->assign('cates',D('Threadcate')->fetchAll());
    }
    
    
    public function index() {
        $Threadpost = D('Threadpost');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Threadpost->where($map)->count(); 
        $Page = new Page($count, 25);
        $show = $Page->show(); 
        $list = $Threadpost->where($map)->order(array('post_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach($list as $k=>$val){
            $user_ids[$val['user_id']] = $val['user_id'];
			$val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        $this->assign('users',D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display(); 
    }


    public function create($thread_id) {
        if ($thread_id = (int) $thread_id) {
            $obj = D('Threadpost');
            if (!$detail = D('Thread')->find($thread_id)) {
                $this->baoError('主题不正确');
            }
            if ($this->isPost()) {
                $data = $this->createCheck();
                $thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['thread_id'] = $thread_id;
                $data['cate_id'] = $detail['cate_id'];
                if ($post_id = $obj->add($data)) {
                    D('Thread')->updateCount($thread_id,'posts');
                    foreach($thumb as $k=>$val){
                        D('Threadpostphoto')->add(array('post_id'=>$post_id,'photo'=>$val));
                    }
                    $this->baoSuccess('操作成功', U('threadpost/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择发帖所属的主题');
        }
    }
    
    private function createCheck() {
		$data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('话题标题不能为空');
        }
		 $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('话题简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('话题简介含有敏感词：' . $words);
        }
        $data['audit'] = 1;
		$data['orderby'] = (int) $data['orderby'];
        $data['is_fine'] = (int) $data['is_fine'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    
    
    public function edit($post_id = 0) {

        if ($post_id = (int) $post_id) {
            $obj = D('Threadpost');
            if (!$detail = $obj->find($post_id)) {
                $this->baoError('请选择要编辑的话题');
            }
            $Thread = D('Thread')->find($detail['thread_id']);
            if ($this->isPost()) {
                $data = $this->editCheck();
                $thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['post_id'] = $post_id;
                $data['cate_id'] = $Thread['cate_id'];
                if (false !== $obj->save($data)) {
                    D('Threadpostphoto')->where(array('post_id'=>$post_id))->delete();
                    foreach($thumb as $k=>$val){
                        D('Threadpostphoto')->add(array('post_id'=>$post_id,'photo'=>$val));
                    }
                    $this->baoSuccess('操作成功', U('threadpost/index'));
                }
                $this->baoError('操作失败');
            } else {
                $thumb = D('Threadpostphoto')->where(array('post_id'=>$post_id))->select();
                $this->assign('thumb', $thumb);
                $this->assign('detail', $detail);
				$this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的话题');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('话题标题不能为空');
        }
		 $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('话题简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('话题简介含有敏感词：' . $words);
        } 
        $data['audit'] = 1;
		$data['orderby'] = (int) $data['orderby'];
        $data['is_fine'] = (int) $data['is_fine'];
        return $data;
    }
    
    public function audit($post_id = 0) {
        $obj = D('Threadpost');
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj->save(array('post_id' => $post_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('threadpost/index'));
        } else {
            $post_id = $this->_post('post_id', false);
            if (is_array($post_id)) {
                foreach ($post_id as $id) {
                    $obj->save(array('post_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('threadpost/index'));
            }
            $this->baoError('请选择要审核的话题');
        }
    }
    
    
    public function delete($post_id = 0) {
        $obj = D('Threadpost');
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj->save(array('post_id' => $post_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('threadpost/index'));
        } else {
            $post_id = $this->_post('post_id', false);
            if (is_array($post_id)) {
                foreach ($post_id as $id) {
                    $obj->save(array('post_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('threadpost/index'));
            }
            $this->baoError('请选择要删除的话题');
        }
    }
    
}
