<?php

class CommunitypostsAction extends CommonAction {

    private $create_fields = array('title', 'user_id', 'cate_id', 'details','orderby','is_fine', 'create_time', 'create_ip');
    private $edit_fields = array('title', 'user_id', 'cate_id', 'details','orderby','is_fine');

    public function index() {


        $Post = D('Communityposts');
        import('ORG.Util.Page');
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
       
     
        if($audit = (int)$this->_param('audit')){
            $map['audit'] = ($audit === 1 ? 1:0);
            $this->assign('audit',$audit);
        }
        $count = $Post->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Post->where($map)->order(array('post_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		
        $ids = $communitys = array();
        foreach($list as $k=>$val){
        
            if($val['user_id']){
                $ids[$val['user_id']] = $val['user_id'];
				$communitys[$val['community_id']]=$val['community_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
		
		$this->assign('communitys',D('Community')->itemsByIds($communitys));
        $this->assign('users',D('Users')->itemsByIds($ids));
        $this->assign('list', $list); 
		$this->assign('sharecate', $list2);
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Communityposts');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('communityposts/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        } $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        } $data['create_time'] = NOW_TIME;

        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int)$data['orderby'];
        $data['is_fine'] = (int)$data['is_fine'];
        return $data;
    }

    public function edit($post_id = 0) {
        if ($post_id = (int) $post_id) {
            $obj = D('Communityposts');
            if (!$detail = $obj->find($post_id)) {
                $this->baoError('请选择要编辑的消费分享');
            }
            if ($this->isPost()) {
                $form_data = $_POST;
                $data = $this->editCheck();
                $data['post_id'] = $post_id;
                $data['community_id'] = $form_data['data']['community_id'];
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('communityposts/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
				$photos = explode(',',$detail['gallery']);
            	$this->assign('photos', $photos);
                $this->assign('user',D('Users')->find($detail['user_id']));
                $this->assign('community',D('Community')->find($detail['community_id']));//查询小区名字
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的消费分享');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        } 
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        } 
		//图片处理上传开始
        $photos = $this->_post('photos', false);
            $photo1 = $val1 = '';
            if (!empty($photos)) {
                foreach ($photos as $val1) {
                    if (isImage($val1) && $val1 != '') {
                        $photo1 = $photo1 . ',' . $val1;
                    }
                }
            }
        $data['gallery'] = ltrim($photo1, ',');
		//图片处理结束
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }	
        $data['orderby'] = (int)$data['orderby'];
        $data['is_fine'] = (int)$data['is_fine'];
        return $data;
    }

    public function delete($post_id = 0) {
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj = D('Communityposts');
            $obj->delete($post_id);
            $this->baoSuccess('删除成功！', U('communityposts/index'));
        } else {
            $post_id = $this->_post('post_id', false);
            if (is_array($post_id)) {
                $obj = D('Communityposts');
                foreach ($post_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('communityposts/index'));
            }
            $this->baoError('请选择要删除的消费分享');
        }
    }

    public function audit($post_id = 0) {
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj = D('Communityposts');
            $detail = $obj->find($post_id);
            $obj->save(array('post_id' => $post_id, 'audit' => 1));
            D('Users')->integral($detail['user_id'],'share');
           // print_r($detail);die;
            $this->baoSuccess('审核成功！', U('communityposts/index'));
        } else {
            $post_id = $this->_post('post_id', false);
            if (is_array($post_id)) {
                $obj = D('Communityposts');
                foreach ($post_id as $id) {
                    $detail = $obj->find($id);
                    $obj->save(array('post_id' => $id, 'audit' => 1));
                    D('Users')->integral($detail['user_id'],'share');
                }
                $this->baoSuccess('审核成功！', U('communityposts/index'));
            }
            $this->baoError('请选择要审核的消费分享');
        }
    }

}
