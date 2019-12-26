<?php
class PostAction extends CommonAction
{
    private $create_fields = array('city_id', 'area_id', 'title', 'user_id', 'cate_id', 'cate_id','pic', 'orderby', 'details','is_fine', 'create_time', 'create_ip');
    private $edit_fields = array('city_id', 'area_id', 'title', 'user_id', 'cate_id', 'pic','details', 'orderby', 'is_fine');
    public function index()
    {
        $Sharecate = D('Sharecate');
        $list2 = $Sharecate->fetchAll();
        $Post = D('Post');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Shopcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Post->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Post->where($map)->order(array('post_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('sharecate', $list2);
        $this->assign('page', $show);
        $this->assign('cates', D('Shopcate')->fetchAll());
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Post');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('post/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('sharecate', D('Sharecate')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
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
            $this->baoError('标题不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
		$Sharecate = D('Sharecate')->where(array('cate_id' => $data['cate_id']))->find();
		$parent_id = $Sharecate['parent_id'];
		if ($parent_id == 0) {
			$this->baoError('必须选择二级分类');
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
        $data['pic'] = ltrim($photo1, ',');
		//图片处理结束
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
			
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        $data['is_fine'] = (int) $data['is_fine'];
        return $data;
    }
    public function edit($post_id = 0){
        if ($post_id = (int) $post_id) {
            $obj = D('Post');
            if (!($detail = $obj->find($post_id))) {
                $this->baoError('请选择要编辑的帖子');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['post_id'] = $post_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('post/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
				$photos = explode(',',$detail['pic']);
                $this->assign('photos', $photos);
                $this->assign('sharecate', D('Sharecate')->fetchAll());
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的帖子');
        }
    }
    private function editCheck()
    {
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
            $this->baoError('标题不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
		$Sharecate = D('Sharecate')->where(array('cate_id' => $data['cate_id']))->find();
		$parent_id = $Sharecate['parent_id'];
		if ($parent_id == 0) {
			$this->baoError('必须选择二级分类');
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
        $data['pic'] = ltrim($photo1, ',');
		//图片处理结束
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }	
			
        $data['orderby'] = (int) $data['orderby'];
        $data['is_fine'] = (int) $data['is_fine'];
        return $data;
    }
    public function delete($post_id = 0){
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj = D('Post');
            $obj->delete($post_id);
            $this->baoSuccess('删除成功！', U('post/index'));
        } else {
            $post_id = $this->_post('post_id', false);
            if (is_array($post_id)) {
                $obj = D('Post');
                foreach ($post_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('post/index'));
            }
            $this->baoError('请选择要删除的帖子');
        }
    }
    public function audit($post_id = 0){
        if (is_numeric($post_id) && ($post_id = (int) $post_id)) {
            $obj = D('Post');
            $detail = $obj->find($post_id);
            $obj->save(array('post_id' => $post_id, 'audit' => 1));
            D('Users')->integral($detail['user_id'], 'share');
            $this->baoSuccess('审核成功！', U('post/index'));
        } else {
            $post_id = $this->_post('post_id', false);
            if (is_array($post_id)) {
                $obj = D('Post');
                foreach ($post_id as $id) {
                    $detail = $obj->find($id);
                    $obj->save(array('post_id' => $id, 'audit' => 1));
                    D('Users')->integral($detail['user_id'], 'share');
                }
                $this->baoSuccess('审核成功！', U('post/index'));
            }
            $this->baoError('请选择要审核的帖子');
        }
    }
}