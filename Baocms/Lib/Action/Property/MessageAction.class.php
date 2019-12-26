<?php
class MessageAction extends CommonAction{
		
    public function index(){
		$Msg = D('Msg');
        import('ORG.Util.Page');
		
		$map['cate_id'] = array('eq',4); 
		$map['closed'] = array('eq',0); 
		
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
		
		$lists = $Msg->where($map)->order(array('create_time' => 'desc'))->select();
        foreach ($lists as $k => $val) {
			 if (!empty($val['community_id'])) {
                $lists[$k]['community_id'] =  $val['community_id'];
                if ($lists[$k]['community_id'] != $this->community_id ) {
                    unset($lists[$k]);
                }
            }

        }
		$count = count($lists);
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);

		
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('types', $Msg->getType());
        $this->display();
    }
    public function detail($msg_id){
        $msg_id = (int) $msg_id;
        D('Msg')->updateCount($msg_id, 'views');
        if (!($detail = D('Msg')->find($msg_id))) {
            $this->error('消息不存在');
        }

		if ($detail['cate_id'] != 4) {
            $this->error('类型错误');
        }
		
		if (!empty($detail['shop_id'])) {//如果表里面会员不为空那么判断ID正常不
            if ($detail['shop_id'] != $this->shop_id) {
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
			
			if ($detail['cate_id'] != 4) {
                $this->error('操作错误');
            }
			
			
			if (!empty($detail['shop_id'])) {//如果表里面会员不为空那么判断ID正常不
				if ($detail['shop_id'] != $this->shop_id) {
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