<?php



class AwardgoodsAction extends CommonAction {

    private $create_fields = array('award_name', 'goods_name', 'prob', 'num', 'surplus');
    private $edit_fields = array('award_name', 'goods_name', 'prob', 'num', 'surplus');

    public function index() {
        $Awardgoods = D('Awardgoods');
        import('ORG.Util.Page'); // 导入分页类 
        $award_id = (int) $this->_get('award_id');
        $map = array('award_id'=>$award_id);
        if (!$detail = D('Award')->find($award_id)) {
            $this->error('参数错误');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('参数错误');
        }
        $this->assign('is_online',$detail['is_online']);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Awardgoods->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Awardgoods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('award_id', $award_id);
        $this->display(); // 输出模板
    }

}
