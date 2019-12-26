<?php
class BillshopAction extends CommonAction
{
    public function index()
    {
        $Billshop = D('Billshop');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['reason'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('reason', $keyword);
        }
        $list_id = (int) ($list_id = $this->_param('list_id'));
        if ($list_id) {
            $map['list_id'] = (int) ($list_id = $this->_param('list_id'));
        }
        $count = $Billshop->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Billshop->where($map)->order(array('list_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        $this->assign('shop', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create($list_id)
    {
        $list_id = (int) $list_id;
        if (empty($list_id)) {
            $this->baoError('请选择榜单');
        }
        if (!($detail = D('Billboard')->find($list_id))) {
            $this->baoError('选择的榜单不存在');
        }
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Billshop');
            $detail = $obj->where(array('shop_id' => $data['shop_id'], 'list_id' => $list_id))->find();
            if (!empty($detail)) {
                $this->baoError('此商家已经上榜过了');
            }
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('billboard/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    public function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), array('shop_id', 'reason', 'list_id', 'orderby'));
        $data['orderby'] = (int) $data['orderby'];
        $data['shop_id'] = (int) $data['shop_id'];
        $data['list_id'] = $this->_param('list_id');
        $data['reason'] = trim(htmlspecialchars($data['reason']));
        if (empty($data['reason'])) {
            $this->baoError('上榜理由不能为空！');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function delete($bill_id = 0)
    {
        if (is_numeric($bill_id) && ($list_id = (int) $bill_id)) {
            $obj = D('Billshop');
            $obj->delete($bill_id);
            $this->baoSuccess('删除成功！', U('billboard/index'));
        } else {
            $list_id = $this->_post('bill_id', false);
            if (is_array($bill_id)) {
                $obj = D('Billshop');
                foreach ($bill_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('billboard/index'));
            }
            $this->baoError('请选择要删除的商家');
        }
    }
}