<?php
class TuanAction extends CommonAction
{
    public function ordercode()
    {
        $Tuancode = D('Tuancode');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
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
    public function coderefund($code_id)
    {
        $code_id = (int) $code_id;
        if ($detail = D('Tuancode')->find($code_id)) {
            if ($detail['user_id'] != $this->uid) {
                $this->baoError('非法操作');
            }
            if ($detail['status'] != 0 || $detail['is_used'] != 0) {
                $this->baoError('该套餐码不能申请退款');
            }
            if (D('Tuancode')->save(array('code_id' => $code_id, 'status' => 1))) {
                $this->baoSuccess('申请成功！等待网站客服处理！', U('members/ordercode'));
            }
        }
        $this->baoError('操作失败');
    }
}