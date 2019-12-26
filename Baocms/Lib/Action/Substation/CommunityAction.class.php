<?php
class CommunityAction extends CommonAction
{
    private $create_fields = array('name', 'addr', 'tel', 'pic', 'user_id', 'city_id', 'area_id', 'village_id', 'property', 'lng', 'lat', 'orderby');
    private $edit_fields = array('name', 'addr', 'tel', 'pic', 'user_id', 'city_id', 'area_id', 'village_id', 'property', 'lng', 'lat', 'orderby');
    private $tieba_create_fields = array('title', 'user_id', 'cate_id', 'details', 'orderby', 'is_fine', 'create_time', 'create_ip');
    private $tieba_edit_fields = array('title', 'user_id', 'cate_id', 'details', 'orderby', 'is_fine');
    public function index()
    {
        $Community = D('Community');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('city_id' => $this->city_id);
        $users = $this->_param('data', false);
        if ($users['user_id']) {
            $map['user_id'] = $users['user_id'];
        }
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|addr'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $Community->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Community->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $Community->_format($val);
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        //数据查询开始
        $Village = D('Village');
        $village_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $Village->_format($val);
            $village_ids[$val['village_id']] = $val['village_id'];
        }
        if (!empty($village_ids)) {
            $this->assign('village', D('Village')->itemsByIds($village_ids));
        }
        //数据查询结束
        $this->assign('keyword', $keyword);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Community');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('community/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('areas', D('Area')->fetchAll());
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('小区名称不能为空');
        }
        $data['property'] = htmlspecialchars($data['property']);
        if (empty($data['property'])) {
            $this->baoError('物业公司不能为空');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('小区地址不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        //增加开始
        $data['village_id'] = (int) $data['village_id'];
        if (empty($data['village_id'])) {
            $this->baoError('所属村镇不能为空');
        }
        //增加结束
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('物业管理员不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($community_id = 0)
    {
        if ($community_id = (int) $community_id) {
            $obj = D('Community');
            if (!($detail = $obj->find($community_id))) {
                $this->baoError('请选择要编辑的小区管理');
            }
            if ($detail['city_id'] != $this->city_id) {
                $this->error('非法操作', U('community/index'));
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['community_id'] = $community_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('community/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('users', D('Users')->find($detail['user_id']));
                $this->assign('villages', D('village')->find($detail['village_id']));
                $this->assign('areas', D('Area')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商圈管理');
        }
    }
    public function hots($business_id)
    {
        if ($business_id = (int) $business_id) {
            $obj = D('Business');
            if (!($detail = $obj->find($business_id))) {
                $this->baoError('请选择商圈');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            $obj->save(array('business_id' => $business_id, 'is_hot' => $detail['is_hot']));
            $obj->cleanCache();
            $this->baoSuccess('操作成功', U('business/index'));
        } else {
            $this->baoError('请选择商圈');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('小区名称不能为空');
        }
        $data['property'] = htmlspecialchars($data['property']);
        if (empty($data['property'])) {
            $this->baoError('物业公司不能为空');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('小区地址不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        $data['village_id'] = (int) $data['village_id'];
        if (empty($data['village_id'])) {
            $this->baoError('所属村镇不能为空');
        }
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('物业管理员不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        return $data;
    }
    public function delete($community_id = 0)
    {
        if (is_numeric($community_id) && ($community_id = (int) $community_id)) {
            $obj = D('Community');
            $citys = $obj->find($community_id);
            if ($citys['city_id'] != $this->city_id) {
                $this->error('非法操作', U('useraddr/index'));
            }
            $obj->delete($community_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('community/index'));
        } else {
            $community_id = $this->_post('community_id', false);
            if (is_array($community_id)) {
                $obj = D('Community');
                foreach ($community_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('community/index'));
            }
            $this->baoError('请选择要删除的小区管理');
        }
    }
    public function child($area_id = 0)
    {
        $datas = D('Community')->fetchAll();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['community_id'] . '">' . $val['name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
    // 新增选择小区
    public function select()
    {
        $User = D('Community');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('closed' => array('IN', '0,-1'), 'city_id' => $this->city_id);
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        if ($nickname = $this->_param('nickname', 'htmlspecialchars')) {
            $map['nickname'] = array('LIKE', '%' . $nickname . '%');
            $this->assign('nickname', $nickname);
        }
        if ($ext0 = $this->_param('ext0', 'htmlspecialchars')) {
            $map['ext0'] = array('LIKE', '%' . $ext0 . '%');
            $this->assign('ext0', $ext0);
        }
        $count = $User->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 8);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $pager = $Page->show();
        // 分页显示输出
        $list = $User->where($map)->order(array('Community_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $pager);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
}