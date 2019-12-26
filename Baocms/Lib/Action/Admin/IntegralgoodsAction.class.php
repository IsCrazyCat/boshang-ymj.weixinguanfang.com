<?php
class IntegralgoodsAction extends CommonAction
{
    private $create_fields = array('title', 'shop_id', 'face_pic', 'integral', 'price', 'num', 'limit_num', 'exchange_num', 'details', 'orderby', 'create_time', 'create_ip');
    private $edit_fields = array('title', 'shop_id', 'face_pic', 'integral', 'price', 'num', 'limit_num', 'exchange_num', 'details', 'orderby', 'create_time', 'create_ip');
    public function index(){
        $Integralgoods = D('Integralgoods');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = (int) $this->_param('shop_id')) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Integralgoods->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Integralgoods->where($map)->order(array('goods_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Integralgoods');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('integralgoods/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
        }
        $data['face_pic'] = htmlspecialchars($data['face_pic']);
        if (empty($data['face_pic'])) {
            $this->baoError('请上传产品图片');
        }
        if (!isImage($data['face_pic'])) {
            $this->baoError('产品图片格式不正确');
        }
        $data['integral'] = (int) $data['integral'];
        if (empty($data['integral'])) {
            $this->baoError('兑换积分不能为空');
        }
        $data['price'] = (int) $data['price'];
        if (empty($data['price'])) {
            $this->baoError('市场价格不能为空');
        }
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('库存数量不能为空');
        }
        $data['limit_num'] = (int) $data['limit_num'];
        if (empty($data['limit_num'])) {
            $this->baoError('限制单用户兑换数量不能为空');
        }
        $data['exchange_num'] = (int) $data['exchange_num'];
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商品介绍不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商品介绍含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function edit($goods_id = 0){
        if ($goods_id = (int) $goods_id) {
            $obj = D('Integralgoods');
            if (!($detail = $obj->find($goods_id))) {
                $this->baoError('请选择要编辑的积分商品');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['goods_id'] = $goods_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('integralgoods/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
				$this->assign('shop',D('Shop')->find($detail['shop_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的积分商品');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('产品名称不能为空');
        }
        $data['face_pic'] = htmlspecialchars($data['face_pic']);
        if (empty($data['face_pic'])) {
            $this->baoError('请上传产品图片');
        }
        if (!isImage($data['face_pic'])) {
            $this->baoError('产品图片格式不正确');
        }
        $data['integral'] = (int) $data['integral'];
        if (empty($data['integral'])) {
            $this->baoError('兑换积分不能为空');
        }
        $data['price'] = (int) $data['price'];
        if (empty($data['price'])) {
            $this->baoError('市场价格不能为空');
        }
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('库存数量不能为空');
        }
        $data['limit_num'] = (int) $data['limit_num'];
        if (empty($data['limit_num'])) {
            $this->baoError('限制单用户兑换数量不能为空');
        }
        $data['exchange_num'] = (int) $data['exchange_num'];
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('商品介绍不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('商品介绍含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    public function delete($goods_id = 0){
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Integralgoods');
            $obj->save(array('goods_id' => $goods_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('integralgoods/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('Integralgoods');
                foreach ($goods_id as $id) {
                    $obj->save(array('goods_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('integralgoods/index'));
            }
            $this->baoError('请选择要删除的积分商品');
        }
    }
    public function audit($goods_id = 0){
        if (is_numeric($goods_id) && ($goods_id = (int) $goods_id)) {
            $obj = D('Integralgoods');
            $obj->save(array('goods_id' => $goods_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('integralgoods/index'));
        } else {
            $goods_id = $this->_post('goods_id', false);
            if (is_array($goods_id)) {
                $obj = D('Integralgoods');
                foreach ($goods_id as $id) {
                    $obj->save(array('goods_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('integralgoods/index'));
            }
            $this->baoError('请选择要审核的积分商品');
        }
    }
}