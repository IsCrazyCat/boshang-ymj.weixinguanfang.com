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
    
    
    public function create($award_id) {
        $award_id = (int) $award_id;
        if (!$detail = D('Award')->find($award_id)) {
            $this->baoError('参数错误');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->baoError('参数错误');
        }
        
        if ($this->isPost()) {
            $data = $this->createCheck();
            
            $obj = D('Awardwinning');
            $data['award_id'] = $award_id;
            if ($obj->add($data)) {
                D('Awardgoods')->updateCount($data['goods_id'],'surplus',-1);
                $this->baoSuccess('添加成功', U('awardwinning/index',array('award_id'=>$award_id)));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('award_id', $award_id);
            $this->assign('goods',D('Awardgoods')->where(array('award_id'=>$award_id))->select());
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['goods_id'] = (int) $data['goods_id'];
        if (empty($data['goods_id'])) {
            $this->baoError('商品不能为空');
        } 
        
         if (!$goods = D('Awardgoods')->find($data['goods_id'])) {
            $this->baoError('参数错误');
        }
        if(!$detail = D('Award')->find($goods['award_id'])){
             $this->baoError('参数错误');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->baoError('参数错误');
        }
        
        
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('姓名不能为空');
        } 
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        } 
        $data['randstr'] =rand(1,10000);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }


}