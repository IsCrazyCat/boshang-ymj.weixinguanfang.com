<?php
class AreaAction extends CommonAction
{
    private $create_fields = array('area_name', 'city_id', 'orderby');
    private $edit_fields = array('area_name', 'city_id', 'orderby');
    public function index()
    {
        $Area = D('Area');
        import('ORG.Util.Page');
        // 导入分页类
        $mapss = array('city_id' => $this->city_id);
        //查询城市ID为当前登录账户的ID
        $city_ids = D('City')->where($mapss)->order(array('city_id' => 'desc'))->select();
        //查询所在城市的商家
        foreach ($city_ids as $val) {
            $cityids[$val['city_id']] = $val['city_id'];
            //对比shop_id
        }
        $maps['city_id'] = array('in', $cityids);
        //取得当前商家ID，给下面的maps查询
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $maps['area_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $this->assign('keyword', $keyword);
        $city_id = (int) $this->_param('city_id');
        if ($city_id) {
            $maps['city_id'] = $city_id;
        }
        $this->assign('city_id', $city_id);
        $count = $Area->where($maps)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Area->where($maps)->order(array('area_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('citys', D('City')->fetchAll());
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Area');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('area/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('citys', $citys = D('City')->where(array('closed' => 0, 'city_id' => $this->city_id))->select());
            //这里应该查询fetchAll不过有缓存会错
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['area_name'] = htmlspecialchars($data['area_name']);
        if (empty($data['area_name'])) {
            $this->baoError('区域名称不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['city_id'] = (int) $data['city_id'];
        return $data;
    }
    public function edit($area_id = 0)
    {
        //查询上级ID编辑处代码开始
        $area_ids = D('Area')->find($area_id);
        $citys = $area_ids['city_id'];
        if ($citys != $this->city_id) {
            $this->error('非法操作', U('area/index'));
        }
        //查询上级ID编辑处代结束
        if ($area_id = (int) $area_id) {
            $obj = D('Area');
            if (!($detail = $obj->find($area_id))) {
                $this->baoError('请选择要编辑的区域管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['area_id'] = $area_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('area/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('citys', $citys = D('City')->where(array('closed' => 0, 'city_id' => $this->city_id))->select());
                //这里应该查询fetchAll不过有缓存会
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的区域管理');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['area_name'] = htmlspecialchars($data['area_name']);
        if (empty($data['area_name'])) {
            $this->baoError('区域名称不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['city_id'] = (int) $data['city_id'];
        return $data;
    }
    public function delete($area_id = 0)
    {
        //查询上级ID编辑处代码开始
        $area_ids = D('Area')->find($area_id);
        $citys = $area_ids['city_id'];
        if ($citys != $this->city_id) {
            $this->error('非法操作', U('area/index'));
        }
        //查询上级ID编辑处代结束
        if (is_numeric($area_id) && ($area_id = (int) $area_id)) {
            $obj = D('Area');
            $obj->delete($area_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('area/index'));
        } else {
            $area_id = $this->_post('area_id', false);
            if (is_array($area_id)) {
                $obj = D('Area');
                foreach ($area_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('area/index'));
            }
            $this->baoError('请选择要删除的区域管理');
        }
    }
}