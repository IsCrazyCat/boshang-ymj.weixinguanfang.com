<?php
class UserrankAction extends CommonAction
{
    private $create_fields = array('rank_name', 'icon', 'icon1', 'prestige', 'rebate');
    private $edit_fields = array('rank_name', 'icon', 'icon1', 'prestige', 'rebate');
    public function index()
    {
        $Userrank = D('Userrank');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        $count = $Userrank->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Userrank->where($map)->order(array('rank_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
            $obj = D('Userrank');
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('userrank/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['rank_name'] = htmlspecialchars($data['rank_name']);
        if (empty($data['rank_name'])) {
            $this->baoError('等级名称不能为空');
        }
        $data['icon'] = htmlspecialchars($data['icon']);
        if (empty($data['icon'])) {
            $this->baoError('等级图标不能为空');
        }
        $data['icon1'] = htmlspecialchars($data['icon1']);
        $data['prestige'] = (int) $data['prestige'];
        $data['rebate'] = (int) $data['rebate'];
        return $data;
    }
    public function edit($rank_id = 0)
    {
        if ($rank_id = (int) $rank_id) {
            $obj = D('Userrank');
            if (!($detail = $obj->find($rank_id))) {
                $this->baoError('请选择要编辑的会员等级');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['rank_id'] = $rank_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('userrank/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的会员等级');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['rank_name'] = htmlspecialchars($data['rank_name']);
        if (empty($data['rank_name'])) {
            $this->baoError('等级名称不能为空');
        }
        $data['icon'] = htmlspecialchars($data['icon']);
        if (empty($data['icon'])) {
            $this->baoError('等级图标不能为空');
        }
        $data['icon1'] = htmlspecialchars($data['icon1']);
        $data['rebate'] = (int) $data['rebate'];
        $data['prestige'] = (int) $data['prestige'];
        return $data;
    }
    public function delete($rank_id = 0)
    {
        if (is_numeric($rank_id) && ($rank_id = (int) $rank_id)) {
            $obj = D('Userrank');
            $obj->delete($rank_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('userrank/index'));
        } else {
            $rank_id = $this->_post('rank_id', false);
            if (is_array($rank_id)) {
                $obj = D('Userrank');
                foreach ($rank_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('userrank/index'));
            }
            $this->baoError('请选择要删除的会员等级');
        }
    }
}