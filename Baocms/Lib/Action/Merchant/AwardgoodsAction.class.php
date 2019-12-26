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

    public function create($award_id) {
        $award_id = (int) $award_id;
        if (!$detail = D('Award')->find($award_id)) {
            $this->error('参数错误');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('参数错误');
        }
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Awardgoods');
            $data['award_id'] = $award_id;
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('awardgoods/index', array('award_id' => $award_id)));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('award_id', $award_id);
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['award_name'] = htmlspecialchars($data['award_name']);
        if (empty($data['award_name'])) {
            $this->baoError('奖项规格不能为空');
        } $data['goods_name'] = htmlspecialchars($data['goods_name']);
        if (empty($data['goods_name'])) {
            $this->baoError('奖品名称不能为空');
        } $data['prob'] = (int) $data['prob'];
        if (empty($data['prob'])) {
            $this->baoError('概率不能为空');
        } $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('数量不能为空');
        }
        $data['surplus'] = $data['num'];

        return $data;
    }

    public function edit($goods_id = 0) {
        if ($goods_id = (int) $goods_id) {
            $obj = D('Awardgoods');
            if (!$detail = $obj->find($goods_id)) {
                $this->baoError('请选择要编辑的奖品设置');
            }

            if (!$detail2 = D('Award')->find($detail['award_id'])) {
                $this->error('参数错误');
            }
            if ($detail2['shop_id'] != $this->shop_id) {
                $this->error('参数错误');
            }

            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['goods_id'] = $goods_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('awardgoods/index', array('award_id' => $detail['award_id'])));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的奖品设置');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['award_name'] = htmlspecialchars($data['award_name']);
        if (empty($data['award_name'])) {
            $this->baoError('奖项规格不能为空');
        } $data['goods_name'] = htmlspecialchars($data['goods_name']);
        if (empty($data['goods_name'])) {
            $this->baoError('奖品名称不能为空');
        } $data['prob'] = (int) $data['prob'];
        if (empty($data['prob'])) {
            $this->baoError('概率不能为空');
        } $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('数量不能为空');
        }
        $data['surplus'] = $data['num'];
        return $data;
    }

    public function delete( $goods_id = 0) {
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Awardgoods');
             if (!$detail = $obj->find($goods_id)) {
                $this->baoError('请选择要编辑的奖品设置');
            }
            
            if (!$detail2 = D('Award')->find($detail['award_id'])) {
                $this->baoError('参数错误');
            }
            if ($detail2['shop_id'] != $this->shop_id) {
                $this->baoError('参数错误');
            }
            
            
            $obj->delete($goods_id);
            $this->baoSuccess('删除成功！', U('awardgoods/index', array('award_id' => $detail['award_id'])));
        } 
    }

}
