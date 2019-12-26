<?php
class HdmobilesignAction extends CommonAction
{
    public function index()
    {
        $Huodongsign = D('Huodongsign');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $huodong_id = (int) $this->_param('huodong_id');
        if ($huodong_id) {
            $map['huodong_id'] = $huodong_id;
        }
        $count = $Huodongsign->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 25);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输
        $list = $Huodongsign->where($map)->order(array('sign_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
}