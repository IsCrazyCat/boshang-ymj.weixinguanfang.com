<?php
class ShopyuyueAction extends CommonAction
{
    private $create_fields = array('user_id', 'shop_id', 'name', 'mobile', 'yuyue_date', 'yuyue_time', 'number', 'create_time', 'create_ip');
    private $edit_fields = array('user_id', 'shop_id', 'name', 'mobile', 'yuyue_date', 'yuyue_time', 'number');
    public function index()
    {
        //修改为单多城市，找到城市id为当前$city_id的商家过滤
        $mapss = array('city_id' => $this->city_id);
        //查询城市ID为当前登录账户的ID
        $shop_city = D('Shop')->where($mapss)->order(array('shop_id' => 'desc'))->select();
        //查询所在城市的商家
        foreach ($shop_city as $val) {
            $cityids[$val['shop_id']] = $val['shop_id'];
            //对比shop_id
        }
        $maps['shop_id'] = array('in', $cityids);
        //取得当前商家ID，给下面的maps查询
        $Shopyuyue = D('Shopyuyue');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $maps['name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $maps['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $maps['user_id'] = $user_id;
            $user = D('Users')->find($user_id);
            $this->assign('nickname', $user['nickname']);
            $this->assign('user_id', $user_id);
        }
        $count = $Shopyuyue->where($maps)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Shopyuyue->where($maps)->order(array('yuyue_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $shop_ids = array();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
            $user_ids[$val['user_id']] = $val['user_id'];
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
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
            $obj = D('Shopyuyue');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('shopyuyue/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('称呼不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        }
        $data['yuyue_date'] = htmlspecialchars($data['yuyue_date']);
        $data['yuyue_time'] = htmlspecialchars($data['yuyue_time']);
        if (empty($data['yuyue_date']) || empty($data['yuyue_time'])) {
            $this->baoError('预定日期不能为空');
        }
        if (!isDate($data['yuyue_date'])) {
            $this->baoError('预定日期格式错误！');
        }
        $data['number'] = (int) $data['number'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['code'] = D('Shopyuyue')->getCode();
        return $data;
    }
    public function edit($yuyue_id = 0)
    {
        if ($yuyue_id = (int) $yuyue_id) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Shopyuyue')->find($yuyue_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('shopyuyue/index'));
            }
            //查询上级ID编辑处代结束
            $obj = D('Shopyuyue');
            if (!($detail = $obj->find($yuyue_id))) {
                $this->baoError('请选择要编辑的商家预约');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['yuyue_id'] = $yuyue_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('shopyuyue/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家预约');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('称呼不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        }
        $data['yuyue_date'] = htmlspecialchars($data['yuyue_date']);
        $data['yuyue_time'] = htmlspecialchars($data['yuyue_time']);
        if (empty($data['yuyue_date']) || empty($data['yuyue_time'])) {
            $this->baoError('预定日期不能为空');
        }
        if (!isDate($data['yuyue_date'])) {
            $this->baoError('预定日期格式错误！');
        }
        $data['number'] = (int) $data['number'];
        return $data;
    }
    public function delete($yuyue_id = 0)
    {
        if (is_numeric($yuyue_id) && ($yuyue_id = (int) $yuyue_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('Shopyuyue')->find($yuyue_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('shopyuyue/index'));
            }
            //查询上级ID编辑处代结束
            $obj = D('Shopyuyue');
            $obj->delete($yuyue_id);
            $this->baoSuccess('删除成功！', U('shopyuyue/index'));
        } else {
            $yuyue_id = $this->_post('yuyue_id', false);
            if (is_array($yuyue_id)) {
                $obj = D('Shopyuyue');
                foreach ($yuyue_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('shopyuyue/index'));
            }
            $this->baoError('请选择要删除的商家预约');
        }
    }
}