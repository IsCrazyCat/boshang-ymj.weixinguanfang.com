<?php
class BusinessAction extends CommonAction
{
    private $create_fields = array('business_name', 'orderby');
    private $edit_fields = array('business_name', 'orderby');
    private $area_id = '';
    public function _initialize()
    {
        parent::_initialize();
        $this->area_id = (int) $_REQUEST['area_id'];
        if (!$this->area_id) {
            $this->error('请选择对应的区域');
        }
        $this->assign('area_id', $this->area_id);
    }
    public function index()
    {
        $Business = D('Business');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('area_id' => $this->area_id);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['business_name'] = array('LIKE', '%' . $keyword . '%');
        }
        $count = $Business->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Business->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $list[$k] = $Business->_format($val);
        }
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
            $obj = D('Business');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('business/index', array('area_id' => $this->area_id)));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['business_name'] = htmlspecialchars($data['business_name']);
        if (empty($data['business_name'])) {
            $this->baoError('商圈名称不能为空');
        }
        $data['area_id'] = $this->area_id;
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($business_id = 0)
    {
        //查询上级ID编辑处代码开始
        $business_ids = D('Business')->find($business_id);
        $area_id = $business_ids['area_id'];
        $area_ids = D('Area')->find($area_id);
        $citys = $area_ids['city_id'];
        if ($citys != $this->city_id) {
            $this->error('非法操作', U('business/index'));
        }
        //查询上级ID编辑处代结束
        if ($business_id = (int) $business_id) {
            $obj = D('Business');
            if (!($detail = $obj->find($business_id))) {
                $this->baoError('请选择要编辑的商圈管理');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['business_id'] = $business_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('business/index', array('area_id' => $this->area_id)));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商圈管理');
        }
    }
    public function hots($business_id)
    {
        //查询上级ID编辑处代码开始
        $business_ids = D('Business')->find($business_id);
        $area_id = $business_ids['area_id'];
        $area_ids = D('Area')->find($area_id);
        $citys = $area_ids['city_id'];
        if ($citys != $this->city_id) {
            $this->error('非法操作', U('business/index'));
        }
        //查询上级ID编辑处代结束
        if ($business_id = (int) $business_id) {
            $obj = D('Business');
            if (!($detail = $obj->find($business_id))) {
                $this->baoError('请选择商圈');
            }
            $detail['is_hot'] = $detail['is_hot'] == 0 ? 1 : 0;
            $obj->save(array('business_id' => $business_id, 'is_hot' => $detail['is_hot']));
            $obj->cleanCache();
            $this->baoSuccess('操作成功', U('business/index', array('area_id' => $this->area_id)));
        } else {
            $this->baoError('请选择商圈');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['business_name'] = htmlspecialchars($data['business_name']);
        if (empty($data['business_name'])) {
            $this->baoError('商圈名称不能为空');
        }
        $data['area_id'] = $this->area_id;
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete()
    {
        if (is_numeric($_GET['business_id']) && ($business_id = (int) $_GET['business_id'])) {
            //查询上级ID编辑处代码开始
            $business_ids = D('Business')->find($business_id);
            $area_id = $business_ids['area_id'];
            $area_ids = D('Area')->find($area_id);
            $citys = $area_ids['city_id'];
            if ($citys != $this->city_id) {
                $this->error('非法操作', U('business/index'));
            }
            //查询上级ID编辑处代结束
            $obj = D('Business');
            $obj->delete($business_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('business/index', array('area_id' => $this->area_id)));
        } else {
            $business_id = $this->_post('business_id', false);
            if (is_array($business_id)) {
                $obj = D('Business');
                foreach ($business_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('business/index', array('area_id' => $this->area_id)));
            }
            $this->baoError('请选择要删除的商圈管理');
        }
    }
    public function child($area_id = 0)
    {
        $areas = D('Area')->where(array('city_id' => $this->city_id))->select();
        foreach ($areas as $val) {
            $area_ids[$val['area_id']] = $val['area_id'];
            //对比shop_id
        }
        $map['area_id'] = array('IN', $area_ids);
        $datas = D('Business')->where($map)->select();
        $str = '<option value="0">请选择</option>';
        foreach ($datas as $val) {
            if ($val['area_id'] == $area_id) {
                $str .= '<option value="' . $val['business_id'] . '">' . $val['business_name'] . '</option>';
            }
        }
        echo $str;
        die;
    }
}