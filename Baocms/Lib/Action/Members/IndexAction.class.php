<?php
class IndexAction extends CommonAction{
    public function index(){
        $Tuancode = D('Tuancode');
        import('ORG.Util.Page'); 
        $map = array('user_id' => $this->uid, 'closed' => 0);
        $status = (int) $this->_param('status');
        switch ($status) {
            case 1:
                break;
            case 2:
                $map['is_used'] = 0;
                break;
            case 3:
                $map['is_used'] = 1;
                break;
        }
        $this->assign('status', $status);
        $count = $Tuancode->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Tuancode->where($map)->order(array('code_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $val) {
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function coderefund($code_id){
        $code_id = (int) $code_id;
        if ($detail = D('Tuancode')->find($code_id)) {
            if ($detail['user_id'] != $this->uid) {
                $this->baoError('非法操作');
            }
            if ($detail['status'] != 0 || $detail['is_used'] != 0) {
                $this->baoError('该套餐码不能申请退款');
            }
            if (!empty($detail)) {
				D('Tuanorder')->save(array('order_id' => $detail['order_id'], 'status' => 3));
				D('Tuancode')->save(array('code_id' => $code_id, 'status' => 1));
				D('Weixintmpl')->weixin_user_refund_shop($code_id,4);//套餐劵申请退款，传订单ID跟类型
                $this->baoSuccess('申请成功！等待网站客服处理！', U('index/index'));
            }
        }
        $this->baoError('操作失败');
    }
    public function delete($code_id = 0){
        //根据套餐码id删除
        if (is_numeric($code_id) && ($code_id = (int) $code_id)) {
            $obj = D('Tuancode');
            if (!($detial = $obj->find($code_id))) {
                $this->baoError('该套餐码不存在');
            }
            if ($detial['user_id'] != $this->uid) {
                $this->baoError('请不要操作他人的订单');
            }
            if ($detial['status'] == 1) {
                $this->baoError('该套餐码暂时不能删除');
            }
            if ($detial['status'] == 0) {
                if ($detial['is_used'] == 0) {
                    $this->baoError('该套餐码暂时不能删除');
                }
            }
            $obj->save(array('code_id' => $code_id, 'closed' => 1));
			D('Weixintmpl')->weixin_delete_order_shop($code_id,4);//套餐劵取消订单，传订单ID跟类型
            $this->baoSuccess('删除成功！', U('index/index'));
        } else {
            $this->baoError('请选择要删除的套餐码');
        }
    }
}