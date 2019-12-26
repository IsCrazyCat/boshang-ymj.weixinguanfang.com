<?php

class YuyueAction extends CommonAction {

    public function index() {
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $status = $this->_param('status', 'htmlspecialchars');
        $this->assign('status', $status);
        $this->assign('nextpage', LinkTo('yuyue/loaddata', array('user_id'=>$this->uid,'status'=>$status, 't' => NOW_TIME,'keyword' => $keyword, 'p' => '0000')));
        $this->display(); // 输出模板
    }

    public function loaddata() {
        $yuyue = D('Shopyuyue');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('user_id' => $this->uid);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
        }
        $status = $this->_param('status', 'htmlspecialchars');
        switch ($status) {
            case 1:
                $map['used'] = 1;
                break;
            case 2:
                $map['used'] = 0;
                break;
        }
        $count = $yuyue->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $yuyue->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            $list[$k] = $val;
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        if (!empty($shop_ids)) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    
    public function delete(){
        $yuyue_id = (int)$this->_param('yuyue_id');
        if(empty($yuyue_id)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'预约不存在'));
        }
        if(!$detail = D('Shopyuyue')->find($yuyue_id)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'预约不存在'));
        }
        if($detail['user_id'] != $this->uid){
            $this->ajaxReturn(array('status'=>'error','msg'=>'不要操作别人的预约'));
        }
        if(D('Shopyuyue')->delete($yuyue_id)){
            $this->ajaxReturn(array('status'=>'success','msg'=>'恭喜您删除成功'));
        }
    }
    
    
}
