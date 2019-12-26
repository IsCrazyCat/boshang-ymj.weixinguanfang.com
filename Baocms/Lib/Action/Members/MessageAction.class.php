<?php
class MessageAction extends CommonAction{
		
    public function index(){
		$Msg = D('Msg');
        import('ORG.Util.Page');
		
		$map['cate_id'] = array('eq',1); 
		$map['closed'] = array('eq',0); 
		
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		$lists = $Msg->where($map)->order(array('create_time' => 'desc'))->select();//时间降序排
        foreach ($lists as $k => $val) {
			 if (!empty($val['user_id'])) {
                $lists[$k]['user_id'] =  $val['user_id'];
                if ($lists[$k]['user_id'] != $this->uid ) {
                    unset($lists[$k]);
                }
            }

        }
		$count = count($lists);// 查询满足要求的总记录数
        $Page = new Page($count, 20);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);

		
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('types', $Msg->getType());//会员中心采用
        $this->display();
    }
    public function detail($msg_id){
        $msg_id = (int) $msg_id;
        D('Msg')->updateCount($msg_id, 'views');
        if (!($detail = D('Msg')->find($msg_id))) {
            $this->error('消息不存在');
        }

		if ($detail['cate_id'] != 1) {
            $this->error('类型错误');
        }
		
		if (!empty($detail['shop_id'])) {//如果表里面会员不为空那么判断ID正常不
            if ($detail['shop_id'] != $this->uid) {
            $this->error('您没有权限查看该消息');
        	}
        }
     
		//连接不等于空自动跳转
        if ($detail['link_url']) {
            header("Location:" . $detail['link_url']);
            die;
        }
        $this->assign('detail', $detail);
        $this->display();
    }
	
	  public function delete($msg_id = 0) {
        if (is_numeric($msg_id) && ($msg_id = (int) $msg_id)) {
            $obj = D('Msg');
            if (!$detail = $obj->find($msg_id)) {
                $this->error('请选择要删除的消息');
            }
            if ($detail['closed'] == 1) {
                $this->error('该消息不存在');
            }
			
			if ($detail['cate_id'] != 1) {
                $this->error('操作错误');
            }
			
			
			if (!empty($detail['shop_id'])) {//如果表里面会员不为空那么判断ID正常不
				if ($detail['shop_id'] != $this->uid) {
					$this->error('您没有权限查看该消息');
				}
            }

            $obj->save(array('msg_id' => $msg_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('message/index'));
        } else {
            $this->baoError('请选择要删除的消息');
        }
    }

}