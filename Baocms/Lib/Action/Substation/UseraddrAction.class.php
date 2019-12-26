<?php
class UseraddrAction extends CommonAction
{
    private $create_fields = array('user_id', 'city_id', 'area_id', 'business_id', 'name', 'mobile', 'addr');
    private $edit_fields = array('user_id', 'city_id', 'area_id', 'business_id', 'name', 'mobile', 'addr');
    public function index()
    {
        $Useraddr = D('Useraddr');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('city_id' => $this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|mobile|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        if ($business_id = (int) $this->_param('business_id')) {
            $map['business_id'] = $business_id;
            $this->assign('business_id', $business_id);
        }
        $count = $Useraddr->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Useraddr->where($map)->order(array('addr_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $areas = D('Area')->where(array('city_id' => $this->city_id))->select();
        $this->assign('areas', $areas);
        foreach ($areas as $val) {
            $area_ids[$val['area_id']] = $val['area_id'];
            //对比shop_id
        }
        $map['area_id'] = array('IN', $area_ids);
        $this->assign('business', D('Business')->where($map)->select());
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Useraddr');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('useraddr/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('areas', D('Area')->fetchAll());
            $this->assign('business', D('Business')->fetchAll());
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['city_id'] = $this->city_id;
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('商圈不能为空');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('收货人不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机号码不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机号码格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('具体地址不能为空');
        }
        return $data;
    }
    public function edit($addr_id = 0)
    {
        if ($addr_id = (int) $addr_id) {
            $obj = D('Useraddr');
            if (!($detail = $obj->find($addr_id))) {
                $this->baoError('请选择要编辑的商家地址');
            }
            if ($detail['city_id'] != $this->city_id) {
                $this->error('非法操作', U('useraddr/index'));
            }
            //查询上级ID编辑处代结束
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['addr_id'] = $addr_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('useraddr/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $areas = D('Area')->where(array('city_id' => $this->city_id))->select();
                $this->assign('areas', $areas);
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家地址');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['city_id'] = $this->city_id;
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('商圈不能为空');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('收货人不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机号码不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机号码格式不正确');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('具体地址不能为空');
        }
        return $data;
    }
    public function delete($addr_id = 0)
    {
        if (is_numeric($addr_id) && ($addr_id = (int) $addr_id)) {
            $obj = D('Useraddr');
            $citys = $obj->find($addr_id);
            if ($citys['city_id'] != $this->city_id) {
                $this->error('非法操作', U('useraddr/index'));
            }
            $obj->delete($addr_id);
            $this->baoSuccess('删除成功！', U('useraddr/index'));
        } else {
            $addr_id = $this->_post('addr_id', false);
            if (is_array($addr_id)) {
                $obj = D('Useraddr');
                foreach ($addr_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('useraddr/index'));
            }
            $this->baoError('请选择要删除的收货地址');
        }
    }
}