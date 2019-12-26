<?php

class PshopAction extends CommonAction {

    private $create_fields = array('id', 'name','tel', 'logo', 'address', 'user_id', 'add_time','mianyunfei','tongchen');
    private $edit_fields = array('id', 'name','tel', 'logo', 'address', 'user_id','mianyunfei','tongchen');

    public function index() {
        $Shop = D('Pshop');
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
        $count = $Shop->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Shop->order(array('id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
		//order(array('goods_id' => 'desc'))
		
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Pshop');
            if ($shop_id = $obj->add($data)) {
               $this->baoSuccess('添加成功', U('pshop/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
        $shop = D('Pshop')->find(array('where' => array('user_id' => $data['user_id'])));
        if (!empty($shop)) {
            $this->baoError('该管理者已经拥有商铺了');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('商铺名称不能为空');
        } $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传商铺LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('商铺LOGO格式不正确');
        } 
        $data['address'] = htmlspecialchars($data['address']);
        if (empty($data['address'])) {
            $this->baoError('店铺地址不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel']) && empty($data['mobile'])) {
            $this->baoError('店铺电话不能为空');
        }
		$data['tongchen'] = htmlspecialchars($data['tongchen']);
		$data['mianyunfei'] = (int) $data['mianyunfei'];
        $data['add_time'] = NOW_TIME;
        return $data;
    }

    public function edit($shop_id = 0) {

        if ($shop_id = (int) $shop_id) {
            $obj = D('Pshop');
            if (!$detail = $obj->find($shop_id)) {
                $this->baoError('请选择要编辑的商家');
            }
            if ($this->isPost()) {
                $data = $this->editCheck($shop_id);
                $data['id'] = $shop_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('pshop/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家');
        }
    }

    private function editCheck($shop_id) {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('商铺名称不能为空');
        } $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传商铺LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('商铺LOGO格式不正确');
        } 
        $data['address'] = htmlspecialchars($data['address']);
        if (empty($data['address'])) {
            $this->baoError('店铺地址不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        if (empty($data['tel']) && empty($data['mobile'])) {
            $this->baoError('店铺电话不能为空');
        }
		$data['tongchen'] = htmlspecialchars($data['tongchen']);
		$data['mianyunfei'] = (int) $data['mianyunfei'];
        return $data;
    }

    public function delete($shop_id = 0) {
        if (is_numeric($shop_id) && ($shop_id = (int) $shop_id)) {
            $obj = D('Pshop');
            $obj->delete($shop_id);
            $this->baoSuccess('删除成功！', U('shop/index'));
        } else {
            $shop_id = $this->_post('id', false);
            if (is_array($shop_id)) {
                $obj = D('Pshop');
                foreach ($shop_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('pshop/index'));
            }
            $this->baoError('请选择要删除的商家');
        }
    }
    public function select() {
        $Shop = D('Pshop');
        import('ORG.Util.Page'); // 导入分页类
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Shop->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Shop->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {

            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
}
