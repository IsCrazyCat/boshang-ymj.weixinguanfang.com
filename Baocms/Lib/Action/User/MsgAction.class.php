<?php



class MsgAction extends CommonAction {

    public function index() {

        $message = D('Usermessage');
        import('ORG.Util.Page'); // 导入分页类 
        $map['user_id'] = $this->uid;
        $map['from_id'] = $this->uid;
        $map['_logic'] = 'OR';
        $lists = $message->where($map)->order(array('message_id' => 'desc'))->select();
        $user_ids = $from_ids = array();
        foreach ($lists as $k => $val) {
            if (!empty($val['user_id'])) {
                $user_ids[$val['user_id']] = $val['user_id'];
                $from_ids[$val['from_id']] = $val['from_id'];
            }
        }
        $all_ids = $user_ids + $from_ids;
        unset($all_ids[$this->uid]);
        $alls = D('Users')->itemsByIds($all_ids);
        $count = count($alls);  // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出  
        $list = array_slice($alls, $Page->firstRow, $Page->listRows);
        foreach ($list as $k => $val) {
            foreach ($lists as $kk => $v) {
                if ($v['user_id'] == $val['user_id']) {
                    $list[$k]['time1'] = $v['create_time'];
                }
                if ($v['from_id'] == $val['user_id']) {
                    $list[$k]['time2'] = $v['create_time'];
                }
                $list[$k]['time'] = $list[$k]['time1'] >= $list[$k]['time2'] ? $list[$k]['time1'] : $list[$k]['time2'];
            }
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();
    }

    public function mlist() {
        $uid = (int) $this->_param('uid');
        if (empty($uid)) {
            $this->error('用户不存在');
        }
        $message = D('Usermessage');
        import('ORG.Util.Page'); // 导入分页类 
        $list1 = $message->where(array('user_id' => $this->uid, 'from_id' => $uid))->order(array('message_id' => 'desc'))->select();
        foreach ($list1 as $k => $val) {
            $list11[$val['message_id']] = $val;
            $list11[$val['message_id']]['send'] = 1;  //该用户发送消息给我
        }
        $list2 = $message->where(array('user_id' => $uid, 'from_id' => $this->uid))->order(array('message_id' => 'desc'))->select();
        foreach ($list2 as $k => $val) {
            $list22[$val['message_id']] = $val;
            $list22[$val['message_id']]['send'] = 0; //我发消息给该用户
        }
        if(empty($list11) && empty($list22)){
            $lists = array();
        }elseif(empty($list11)&&!empty ($list22)){
            $lists = $list22;
        }elseif(empty($list22)&&!empty ($list11)){
            $lists = $list11;
        }else{
            $lists = $list11 + $list22;
        }
        arsort($lists);
        $count = count($lists);  // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出  
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $user_ids = $from_ids = array();
        foreach ($list as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
            $from_ids[$val['from_id']] = $val['from_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('froms', D('Users')->itemsByIds($from_ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('uid', $uid);
        $this->display();
    }

    public function reply() {
        if (IS_AJAX) {
            $uid = (int) $this->_param('uid');
            if (empty($uid)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '用户不存在'));
            }
            $content = htmlspecialchars($_POST['content']);
            if (empty($content)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '请填写回复内容'));
            }
            if (D('Usermessage')->add(array('user_id' => $uid, 'from_id' => $this->uid, 'content' => $content, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip()))) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '回复成功'));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '回复失败'));
            }
        }
    }

}
