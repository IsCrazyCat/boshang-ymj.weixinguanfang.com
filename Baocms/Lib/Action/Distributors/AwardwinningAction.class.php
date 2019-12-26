<?php



class AwardwinningAction extends CommonAction {
    private $create_fields = array( 'goods_id', 'name', 'mobile');
    public function index() {
        $Awardwinning = D('Awardwinning');
        import('ORG.Util.Page'); // 导入分页类 
        $award_id = (int) $this->_get('award_id');
        $map = array('award_id' => $award_id);
        $this->assign('award_id', $award_id);
        if (!$detail = D('Award')->find($award_id)) {
            $this->error('参数错误');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('参数错误');
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['name|mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Awardwinning->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Awardwinning->where($map)->order(array('winning_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $goods_ids = array();
        foreach ($list as $val) {
            if (!empty($val['goods_id'])) {
                $goods_ids[$val['goods_id']] = $val['goods_id'];
            }
        }
        $this->assign('goods', D('Awardgoods')->itemsByIds($goods_ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

}