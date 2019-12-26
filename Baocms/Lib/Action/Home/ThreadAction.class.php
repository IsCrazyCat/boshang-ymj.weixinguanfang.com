<?php


class ThreadAction extends CommonAction {

    public function _initialize() {
        parent::_initialize();
		 if ($this->_CONFIG['operation']['thread'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
		$this->assign('cates',D('Threadcate')->fetchAll());

    }

    public function index(){
        $Threadpost = D('Threadpost');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('audit' => 1, 'closed' => 0);
        if($thread_id = (int)$this->_param('thread_id')){
            $map['thread_id'] = $thread_id;
            $this->assign('thread_id',$thread_id);
        }
        $count = $Threadpost->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $list = $Threadpost->where($map)->order(array('last_time'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $thread_ids = $post_ids = $user_ids = array();
        foreach($list as $k=>$val){
            $thread_ids[$val['thread_id']] = $val['thread_id'];
            $post_ids[$val['post_id']] = $val['post_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('Threads',D('Thread')->itemsByIds($thread_ids));
        $this->assign('users',D('Users')->itemsByIds($user_ids));
        $pics = D('Threadpostphoto')->where(array('post_id'=>array('IN',$post_ids)))->select();
        foreach($list as $k=>$val){
            foreach($pics as $kk=>$v){
                if($val['post_id'] == $v['post_id']){
                    $list[$k]['pics'][] = $v['photo'];
                }
            }
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
		$this->display();
	}
    
    
    public function focus(){
        $Threadpost = D('Threadpost');
        import('ORG.Util.Page'); 
        $map = array('audit' => 1, 'closed' => 0);
        $collect = D('Threadcollect')->where(array('user_id'=>$this->uid))->select();
        $tr_ids = array();
        foreach($collect as $k=>$val){
            $tr_ids[] = $val['thread_id'];
        }
        $this->assign('collect',D('Thread')->where(array('thread_id'=>array('IN',$tr_ids)))->select());
        if($thread_id = (int)$this->_param('thread_id')){
            $map['thread_id'] = $thread_id;
            $this->assign('thread_id',$thread_id);
        }else{
            $map['thread_id'] = array('IN',$tr_ids);
        }
        $count = $Threadpost->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $list = $Threadpost->where($map)->order(array('last_time'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $thread_ids = $post_ids = $user_ids = array();
        foreach($list as $k=>$val){
            $thread_ids[$val['thread_id']] = $val['thread_id'];
            $post_ids[$val['post_id']] = $val['post_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('Threads',D('Thread')->itemsByIds($thread_ids));
        $this->assign('users',D('Users')->itemsByIds($user_ids));
        $pics = D('Threadpostphoto')->where(array('post_id'=>array('IN',$post_ids)))->select();
        foreach($list as $k=>$val){
            foreach($pics as $kk=>$v){
                if($val['post_id'] == $v['post_id']){
                    $list[$k]['pics'][] = $v['photo'];
                }
            }
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
        $this->display();
    }

    public function lists(){
        $Thread = D('Thread');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if($cate_id = (int)$this->_param('cate_id')){
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id',$cate_id);
        }
        
        $count = $Thread->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $list = $Thread->where($map)->order(array('thread_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $result = D('Threadcollect')->where(array('user_id'=>$this->uid))->select();
        
        foreach($list as $k=>$val){
            foreach($result as $kk=>$v){
                if($val['thread_id'] == $v['thread_id']){
                    $list[$k]['collect'] = 1;
                }
            }
        }
        $this->assign('list', $list); 
        $this->assign('page', $show); 
		$this->display();
	}
    
    public function collect() {
        if (empty($this->uid)) {
            $this->ajaxLogin(); 
        }
        $thread_id = (int) $this->_get('thread_id');
        if (!$detail = D('Thread')->find($thread_id)) {
            $this->baoError('没有该部落');
        }
        if ($detail['closed']) {
            $this->baoError('该部落已经被删除');
        }
        if (D('Threadcollect')->check($thread_id, $this->uid)) {
            if(D('Threadcollect')->where(array('thread_id'=>$thread_id,'user_id'=>$this->uid))->delete()){
                D('Thread')->updateCount($thread_id,'fans',-1);
                $this->baoSuccess('取消关注成功！',U('thread/lists',array('cate_id'=>$detail['cate_id'])));
            }
            $this->baoError('取消失败！');
        }else{
            $data = array(
                'thread_id' => $thread_id,
                'user_id' => $this->uid,
            );
            if (D('Threadcollect')->add($data)) {
                D('Thread')->updateCount($thread_id,'fans');
                $this->baoSuccess('恭喜您关注成功！',U('thread/detail',array('thread_id'=>$thread_id)));
            }
            $this->baoError('关注失败！');
        }
    }
    
    public function plsc(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if (IS_AJAX) {
            $thread_ids = $this->_param('thread_ids',false);
            foreach($thread_ids as $k=>$val){
                if(!$res = D('Threadcollect')->where(array('thread_id'=>$val,'user_id'=>$this->uid))->find()){
                    D('Threadcollect')->add(array('user_id'=>$this->uid,'thread_id'=>$val));
                    D('Thread')->updateCount($val,'fans');
                }
            }
            $this->ajaxReturn(array('status' => 'success', 'msg' => '一键关注成功'));
        }
    }

    

    public function detail($thread_id){
        if(!$thread_id = (int)$thread_id){
            $this->error('部落不存在');
        }elseif(!$detail = D('Thread')->find($thread_id)){
            $this->error('部落不存在');
        }elseif($detail['closed'] != 0){
            $this->error('该部落已被删除');
        }else{
            $Threadpost = D('Threadpost');
            import('ORG.Util.Page'); 
            $map = array('closed' => 0,'audit'=>1,'thread_id'=>$thread_id);
            if($order = (int)$this->_param('order')){
                if($order == 2){
                    $orderby = array('post_id'=>'desc');
                }else{
                    $orderby = array('last_time'=>'desc');
                }
            }
            $this->assign('order',$order);
            $count = $Threadpost->where($map)->count(); 
            $Page = new Page($count, 15); 
            $show = $Page->show(); 
            $list = $Threadpost->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $post_ids = $user_ids = array();
            foreach($list as $k=>$val){
                $post_ids[$val['post_id']] = $val['post_id'];
                $user_ids[$val['user_id']] = $val['user_id'];
            }
            $this->assign('users',D('Users')->itemsByIds($user_ids));
            $pics = D('Threadpostphoto')->where(array('post_id'=>array('IN',$post_ids)))->select();
            foreach($list as $k=>$val){
                foreach($pics as $kk=>$v){
                    if($val['post_id'] == $v['post_id']){
                        $list[$k]['pics'][] = $v['photo'];
                    }
                }
            }
            $this->assign('list', $list); 
            $this->assign('page', $show); 
            if($res = D('Threadcollect')->where(array('thread_id'=>$thread_id,'user_id'=>$this->uid))->find()){
                $detail['collect'] = 1;
            }
            $collect = D('Threadcollect')->where(array('user_id'=>$this->uid))->select();
            $tr_ids = array();
            foreach ($collect as $k=>$val){
                $tr_ids[] = $val['thread_id'];
            }            
            $Threads = D('Thread')->where(array('cate_id'=>$detail['cate_id'],'closed'=>0,'thread_id'=>array('NOTIN',$tr_ids)))->limit(3)->select();
            $this->assign('Threads',$Threads);
            $this->assign('detail',$detail);
            $this->display();
        }
	}
    
    
	public function postdetail($post_id){
        if(!$post_id = (int)$post_id){
            $this->error('话题不存在');
        }elseif(!$detail = D('Threadpost')->find($post_id)){
            $this->error('话题不存在');
        }elseif($detail['closed'] != 0||$detail['audit'] !=1){
            $this->error('话题不存在');
        }else{
            import('ORG.Util.Page'); 
           
            D('Threadpost')->updateCount($post_id,'views');
            $pics = D('Threadpostphoto')->where(array('post_id'=>$post_id))->select();
            $this->assign('pics',$pics);
            if (!$res = D('Threadpostzan')->where(array('create_ip' => get_client_ip(), 'post_id' => $post_id))->find()) {
                $detail['is_zan'] = 0;
            } else {
                $detail['is_zan'] = 1;
            }
            //话题推荐
            $tui_list = D('Threadpost')->where(array('thread_id'=>$detail['thread_id'],'closed'=>0,'audit'=>1,'post_id'=>array('NEQ',$post_id)))->order(array('post_id'=>'desc'))->limit(4)->select();
            $this->assign('tui_list',$tui_list);
            //打赏名单
            $donate = D('Threaddonate')->where(array('post_id'=>$post_id))->order(array('donate_id'=>'desc'))->limit(10)->select();
            $uids = array();
            foreach($donate as $k=>$val){
                $uids[$val['user_id']] = $val['user_id'];
            }
            $this->assign('donate',$donate);
            $this->assign('dusers',D('Users')->itemsByIds($uids));
            //回复的帖子
            $reply_list = D('Threadpostcomments')->where(array('post_id'=>$post_id,'type'=>array('IN',array(1,2))))->order(array('comment_id'=>'desc'))->select();
            $user_idss = $comment_idss = array();
            foreach($reply_list as $k=>$val){
                $user_idss[$val['user_id']] = $val['user_id'];
                $comment_idss[$val['comment_id']] = $val['comment_id'];
            }
            $userss = D('Users')->itemsByIds($user_idss);
            $reply_picss =  D('Threadcommentsphoto')->where(array('comment_id'=>array('IN',$comment_idss)))->select();
            foreach($reply_list as $k=>$val){
                $reply_list[$k]['users'] = $userss[$val['user_id']];
                foreach($reply_picss as $kk=>$v){
                    if($val['comment_id'] == $v['comment_id']){
                        $reply_list[$k]['pics'][] = $v; 
                    }
                }
            }
            $lists = D('Threadpostcomments')->where(array('post_id'=>$post_id,'type'=>array('IN',array(0,2))))->order(array('comment_id'=>'asc'))->select();
            $count = count($lists);  
            $Page = new Page($count, 10); 
            $show = $Page->show(); 
            $list = array_slice($lists, $Page->firstRow, $Page->listRows);
            foreach($list as $k=>$val){
                foreach($reply_list as $kk=>$v){
                    if($v['reply_comment_id'] == $val['comment_id']){
                        $list[$k]['replys'][] = $v;
                    }
                }
            }
           
            $user_ids = $comment_ids = array();
            $a = 2;
            foreach ($list as $k => $val) {
                if (!empty($val['user_id'])) {
                    $user_ids[$val['user_id']] = $val['user_id'];
                }
                if($val['comment_id']){
                    $comment_ids[$val['comment_id']] = $val['comment_id'];
                }
                $list[$k]['floor'] = $a;
                $a++;
            }
            $reply_pics = D('Threadcommentsphoto')->where(array('comment_id'=>array('IN',$comment_ids)))->select();
           foreach($list as $k=>$val){
              foreach($reply_pics as $kk=>$v){
                    if($val['comment_id'] == $v['comment_id']){
                        $list[$k]['pics'][] = $v; 
                    }
                }
           }
            $this->assign('list',$list);
            $this->assign('page', $show); 
            $this->assign('userss',$userss);
            $this->assign('users', D('Users')->itemsByIds($user_ids));
            $this->assign('auth',D('Users')->find($detail['user_id']));
            $this->assign('Thread',D('Thread')->find($detail['thread_id']));
            $this->assign('detail',$detail);
            $this->display();
        }
	}
	
	
    public function fabu(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if (IS_AJAX) {
            if(!$thread_id = (int)$this->_param('thread_id')){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '部落不存在'));
            }elseif(!$detail = D('Thread')->find($thread_id)){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '部落不存在'));
            }elseif($detail['closed'] == 1){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该部落已被删除'));
            }else{
                $res = D('Threadpost')->where(array('user_id'=>$this->uid))->order(array('post_id'=>'desc'))->find();
                if(NOW_TIME - $res['create_time'] < 300){
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '请不要频繁发帖'));
                }
                $data['title'] = htmlspecialchars($this->_param('title'));
                if(empty($data['title'])){
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '标题不能为空'));
                }
                if ($words = D('Sensitive')->checkWords($data['title'])) {
                    $this->ajaxReturn(array('status'=>'error','msg'=>'标题含有敏感词：' . $words));
                } 
                $data['details'] = htmlspecialchars($this->_param('details'));
                if(empty($data['details'])){
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '详情不能为空'));
                }
                if ($words2 = D('Sensitive')->checkWords($data['details'])) {
                    $this->ajaxReturn(array('status'=>'error','msg'=>'详情含有敏感词：' . $words2));
                } 
                $photos = $this->_param('photos',false);
                $data['user_id'] = $this->uid;
                $data['cate_id'] = $detail['cate_id'];
                $data['thread_id'] = $thread_id;
                $data['create_time'] = NOW_TIME;
                $data['last_id'] = $this->uid;
                $data['last_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $data['audit'] = $this->_CONFIG['site']['Threadaudit'];
                if($post_id = D('Threadpost')->add($data)){
                    $local = array();
                    foreach ($photos as $val) {
                        if (isImage($val))
                            $local[] = $val;
                    }
                    if (!empty($local)) {
                        D('Threadpostphoto')->upload($post_id, $local);
                    }
                    D('Thread')->updateCount($thread_id,'posts');
                    $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您发帖成功'));
                }
                
            }
        }
    }
	
    public function reply(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if (IS_AJAX) {
            if(!$post_id = (int)$this->_param('post_id')){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该话题不存在'));
            }elseif(!$post = D('Threadpost')->find($post_id)){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该话题不存在'));
            }elseif($post['audit'] !=1||$post['closed']!=0){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '该话题不存在'));
            }else{
                if($comment_id = (int)$this->_param('comment_id')){
                    if(!$detail = D('Threadpostcomments')->find($comment_id)){
                        $this->ajaxReturn(array('status' => 'error', 'msg' => '该评论不存在'));
                    }elseif($detail['closed'] != 0){
                        $this->ajaxReturn(array('status' => 'error', 'msg' => '该评论已被删除'));
                    }else{
                        $data['reply_comment_id'] = $comment_id;
                    }
                }
                $data['contents'] = htmlspecialchars($this->_param('contents'));
                if(empty($data['contents'])){
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '回复内容不能为空'));
                }
                if ($words = D('Sensitive')->checkWords($data['contents'])) {
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '回复内容含有敏感词：' . $words));
                    
                } 
                $data['post_id'] = $post_id;
                $data['type'] = (int)$this->_param('type');
                $data['reply_user_id'] = (int) $this->_param('reply_user_id');
                $data['user_id'] = $this->uid;
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $photos = $this->_param('photos',false);
                if($cid = D('Threadpostcomments')->add($data)){
                    if($photos){
                        foreach($photos as $k=>$val){
                            D('Threadcommentsphoto')->add(array('comment_id'=>$cid,'photo'=>$val));
                        }
                    }
                    D('Threadpost')->updateCount($data['post_id'], 'reply_num');
                    D('Threadpost')->save(array('post_id' => $post_id, 'last_id' => $this->uid, 'last_time' => $data['create_time']));
					D('Users')->prestige($this->uid, 'thread');
                    $this->ajaxReturn(array('status' => 'success', 'msg' => '回复成功'));
                }else{
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '回复失败'));
                }
            }
        }
    }
    
    
    public function zan() {
        if (IS_AJAX) {
            $post_id = (int) $_POST['post_id'];
            if (empty($post_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '话题不存在'));
            }
            $user_id = $this->uid;
            if ($res = D('Threadpostzan')->where(array('post_id' => $post_id, 'create_ip' => get_client_ip()))->find()) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '您已经点过赞了'));
            } else {
                if (D('Threadpostzan')->add(array('post_id' => $post_id, 'user_id' => $user_id, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip()))) {
                    D('Threadpost')->updateCount($post_id, 'zan_num');
                    $this->ajaxReturn(array('status' => 'success', 'msg' => '点赞成功'));
                } else {
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '点赞失败'));
                }
            }
        }
    }

    
    public function donate(){
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        if (IS_AJAX) {
            $post_id = (int) $_POST['post_id'];
            $money = floatval($this->_param('money'));
            if (empty($post_id)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '话题不存在'));
            }elseif(!$detail = D('Threadpost')->find($post_id)){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '话题不存在'));
            }elseif($detail['audit'] !=1||$detail['closed'] !=0){
                $this->ajaxReturn(array('status' => 'error', 'msg' => '话题不存在'));
            }
            if (empty($money)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '打赏金额不正确'));
            }
            $data = array('user_id'=>$this->uid,'money'=>$money,'post_id'=>$post_id,'create_time'=>NOW_TIME,'create_ip'=>get_client_ip());
            if (D('Users')->addMoney($this->uid,-$money*100,'打赏帖子'.$detail['title'])) {
                D('Threaddonate')->add($data);
                D('Threadpost')->updateCount($post_id, 'donate_num');
                if($detail['user_id'] >0){
                    D('Users')->addMoney($detail['user_id'],$money*100,'帖子'.$detail['title'].'被打赏，获得赏金');
                }
                $this->ajaxReturn(array('status' => 'success', 'msg' => '打赏成功'));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '打赏失败'));
            }
            
        }
    }
    
}
