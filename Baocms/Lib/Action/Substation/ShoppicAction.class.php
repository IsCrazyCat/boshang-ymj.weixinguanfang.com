<?php
class ShoppicAction extends CommonAction
{
    public function index()
    {
        $map = array('city_id' => $this->city_id);
        //查询城市ID为当前登录账户的ID
        $shop_city = D('Shop')->where($map)->order(array('shop_id' => 'desc'))->select();
        //查询所在城市的商家
        foreach ($shop_city as $val) {
            $cityids[$val['shop_id']] = $val['shop_id'];
            //对比shop_id
        }
        $maps['shop_id'] = array('in', $cityids);
        //取得当前商家ID，给下面的maps查询
        $Shoppic = D('Shoppic');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Shoppic->where($maps)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Shoppic->where($maps)->order(array('pic_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function delete($pic_id = 0)
    {
        if (is_numeric($pic_id) && ($pic_id = (int) $pic_id)) {
            //查询的上级的上级
            $shop_ids = D('Shoppic')->find($pic_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('business/index'));
            }
            //查询上级ID编辑处代结束
            $obj = D('Shoppic');
            $obj->delete($pic_id);
            $this->baoSuccess('删除成功！', U('shoppic/index'));
        } else {
            $pic_id = $this->_post('pic_id', false);
            if (is_array($pic_id)) {
                $obj = D('Shoppic');
                foreach ($pic_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('shoppic/index'));
            }
            $this->baoError('请选择要删除的商家图片');
        }
    }
    public function audit($pic_id = 0)
    {
        if (is_numeric($pic_id) && ($pic_id = (int) $pic_id)) {
            //查询的上级的上级
            $shop_ids = D('Shoppic')->find($pic_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('business/index'));
            }
            //查询上级ID编辑处代结束
            $obj = D('Shoppic');
            $obj->save(array('pic_id' => $pic_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('shoppic/index'));
        } else {
            $pic_id = $this->_post('pic_id', false);
            if (is_array($pic_id)) {
                $obj = D('Shoppic');
                foreach ($pic_id as $id) {
                    $obj->save(array('pic_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('shoppic/index'));
            }
            $this->baoError('请选择要审核的商家图片');
        }
    }
}