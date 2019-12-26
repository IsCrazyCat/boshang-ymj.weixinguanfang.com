<?php


class PcateAction extends CommonAction {

    private $create_fields = array('name','picurl','csort');
    private $edit_fields = array('name','picurl','csort');

    public function index() {
        $Pcate = D('Pcate');
        import('ORG.Util.Page'); // 导入分页类
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		if($user_id = (int)  $this->_param('user_id')){
           $users = D('Users')->find($user_id);
           $this->assign('nickname',$users['nickname']);
           $this->assign('user_id',$user_id);
           $map['user_id'] = $user_id;
       }
        $list = $Pcate->order(array('id' => 'desc'))->where($map)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Pcate');
            $data['id'] = $id;
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('pcate/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('id',$id);
            $this->display();
        }
    }
    
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('分类不能为空');
        }
        $data['picurl'] = htmlspecialchars($data['picurl']);
        $data['csort'] = (int) $data['csort'];
        return $data;
    }

    public function edit($cate_id = 0) {
        if ($cate_id = (int) $cate_id) {
            $obj = D('Pcate');
            if (!$detail = $obj->find($cate_id)) {
                $this->baoError('请选择要编辑的分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
				$data['id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('pcate/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的分类');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
       
        if (empty($data['name'])) {
            $this->baoError('分类名不能为空');
        }
        $data['picurl'] = htmlspecialchars($data['picurl']);
        $data['csort'] = (int) $data['csort'];
        return $data;
    }

    public function delete($cate_id = 0) {
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Pcate');
            $obj->delete($cate_id);
            $this->baoSuccess('删除成功！', U('pcate/index'));
        } else {
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('Pcate');
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('pcate/index'));
            }
            $this->baoError('请选择要删除的分类');
        }
    }
    

}
