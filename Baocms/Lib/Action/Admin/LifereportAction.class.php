<?php
class LifereportAction extends CommonAction
{
    public function index()
    {
        $Lifereport = D('Lifereport');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        $count = $Lifereport->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Lifereport->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $life_ids = $user_ids = array();
        foreach ($list as $val) {
            $life_ids[$val['life_id']] = $val['life_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('lifes', D('Life')->itemsByIds($life_ids));
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
}