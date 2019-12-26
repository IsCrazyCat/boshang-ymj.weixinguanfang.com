<?php
class WeidianAction extends CommonAction
{
    private $edit_fields = array('user_id', 'cate_id', 'city_id', 'area_id', 'weidian_name', 'addr', 'logo', 'pic', 'business_time', 'details', 'lng', 'lat', 'cate_id', 'audit', 'city_id', 'area_id', 'audit', 'update_time');
    public function _initialize()
    {
        parent::_initialize();
        $cates = D('Weidiancate')->fetchAll();
        $this->assign('cates', $cates);
    }
    public function index()
    {
        $wd = D('WeidianDetails');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0, 'city_id' => $this->city_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['weidian_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $wd->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $wd->order(array('id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $cates = D('Weidiancate')->fetchAll();
        $this->assign('cates', $cates);
        // 赋值数据集
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function audit($wd_id = 0)
    {
        if (is_numeric($wd_id) && ($wd_id = (int) $wd_id)) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('WeidianDetails')->find($wd_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('请不要操作其他人的分站！', U('weidian/index'));
            }
            //查询上级ID编辑处代结束
            $obj = D('WeidianDetails');
            $obj->save(array('id' => $wd_id, 'audit' => 1, 'update_time' => time()));
            $this->baoSuccess('审核成功！', U('weidian/index'));
        } else {
            $error = 0;
            $wd_id = $this->_post('id', false);
            if (is_array($wd_id)) {
                $obj = D('WeidianDetails');
                foreach ($wd_id as $id) {
                    $r = $obj->save(array('id' => $id, 'audit' => 1, 'update_time' => time()));
                    if (!$r) {
                        $error = $error + 1;
                    }
                }
                if ($error > 0) {
                    $this->baoSuccess($error . '条审核失败！', U('weidian/index'));
                } else {
                    $this->baoSuccess('审核成功！', U('weidian/index'));
                }
            }
            $this->baoError('请选择要审核的微店');
        }
    }
    public function edit($shop_id = 0)
    {
        if ($shop_id = (int) $shop_id) {
            //查询上级ID编辑处代码开始
            $shop_ids = D('WeidianDetails')->find($wd_id);
            $shop_id = $shop_ids['shop_id'];
            $city_ids = D('Shop')->find($shop_id);
            $citys = $city_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('请不要操作其他人的分站！', U('weidian/index'));
            }
            //查询上级ID编辑处代结束
            $obj = D('WeidianDetails');
            if (!($detail = $obj->where(array('shop_id' => $shop_id))->find())) {
                $this->baoError('请选择要编辑的微店');
            }
            if ($this->isPost()) {
                $data = $this->editCheck($shop_id);
                $data['shop_id'] = $shop_id;
                //$details = $this->_post('details', 'SecurityEditorHtml');
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->baoError('商家介绍含有敏感词：' . $words);
                }
                $robj = $obj->where('shop_id=' . $shop_id)->save($data);
                if ($robj) {
                    //D('Shopdetails')->upDetails($shop_id, $ex);
                    $this->baoSuccess('操作成功', U('weidian/index'));
                } else {
                    $this->baoError('操作失败' . $obj->getLastSql());
                }
            } else {
                $this->assign('citys', D('City')->fetchAll());
                $this->assign('areas', D('Area')->fetchAll());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('detail', $detail);
                $cates = D('Weidiancate')->fetchAll();
                $this->assign('cates', $cates);
                // 赋值数据集
                //p($cates);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家');
        }
    }
    private function editCheck($shop_id)
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $shop = D('WeidianDetails')->find(array('where' => array('shop_id' => $data['shop_id'])));
        if (!empty($shop) && $shop['shop_id'] != $shop_id) {
            $this->baoError('该管理已经拥有商铺了');
        }
        $data['audit'] = intval($data['audit']);
        if ($shop['audit'] != $data['audit']) {
            $data['update_time'] = time();
        }
        $data['weidian_name'] = htmlspecialchars($data['weidian_name']);
        if (empty($data['weidian_name'])) {
            $this->baoError('微店名称不能为空');
        }
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('店铺地址不能为空');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        $data['business_time'] = htmlspecialchars($data['business_time']);
        if (empty($data['business_time'])) {
            $this->baoError('营业时间不能为空');
        }
        $data['pic'] = htmlspecialchars($data['pic']);
        if (empty($data['pic'])) {
            $this->baoError('请上传微店形象照');
        }
        if (!isImage($data['pic'])) {
            $this->baoError('微店形象照格式不正确');
        }
        $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传微店LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('微店LOGO格式不正确');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id']) || empty($data['city_id'])) {
            $this->baoError('所在城市和区域不能为空');
        }
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        return $data;
    }
    public function select()
    {
        $weidian = D('WeidianDetails');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('audit' => 1);
        if ($weidian_name = $this->_param('weidian_name', 'htmlspecialchars')) {
            $map['weidian_name'] = array('LIKE', '%' . $weidian_name . '%');
            $this->assign('weidian_name', $weidian_name);
        }
        $count = $weidian->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $pager = $Page->show();
        // 分页显示输出
        $list = $weidian->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $pager);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function delete($wd_id = 0)
    {
        if (is_numeric($wd_id) && ($wd_id = (int) $wd_id)) {
            $obj = D('WeidianDetails');
            $obj->save(array('id' => $wd_id, 'closed' => 1));
            $this->baoSuccess('删除微店成功！', U('weidian/index'));
        } else {
            $wd_id = $this->_post('id', false);
            if (is_array($wd_id)) {
                $obj = D('WeidianDetails');
                foreach ($wd_id as $id) {
                    $obj->save(array('id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('批量删除微店成功！', U('weidian/index'));
            }
            $this->baoError('请选择要批量删除的微店');
        }
    }
}