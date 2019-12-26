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
        $linkArr = array();
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        $this->assign('keyword', $keyword);
        $linkArr['keyword'] = $keyword;
        $thread_id = (int) $this->_param('thread_id');
        $this->assign('thread_id', $thread_id);
        $linkArr['thread_id'] = $thread_id;
		$ding = D('Threadpost')->where(array('is_fine' =>1))->order('post_id desc')->limit(0, 5)->select();
        $this->assign('ding', $ding);
        $this->assign('nextpage', LinkTo('thread/loaddata',$linkArr,array('t' => NOW_TIME,'p' => '0000')));
        $this->assign('linkArr',$linkArr);
		$this->display();
	}
    
    public function loaddata(){
        $threadpost = D('Threadpost');
        import('ORG.Util.Page'); 
        $map = array('audit' => 1, 'closed' => 0);
        if($thread_id = (int)$this->_param('thread_id')){
            $map['thread_id'] = $thread_id;
            $this->assign('thread_id',$thread_id);
        }
        
        $count = $threadpost->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $threadpost->where($map)->order(array('last_time'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $thread_ids = $post_ids = $user_ids = array();
        foreach($list as $k=>$val){
            $thread_ids[$val['thread_id']] = $val['thread_id'];
            $post_ids[$val['post_id']] = $val['post_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $this->assign('tribes',D('Thread')->itemsByIds($thread_ids));
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

    
    public function postdetail($post_id){
        if(!$post_id = (int)$post_id){
            $this->error('话题不存在');
        }elseif(!$detail = D('Threadpost')->find($post_id)){
            $this->error('话题不存在');
        }elseif($detail['closed'] != 0||$detail['audit'] !=1){
            $this->error('话题不存在');
        }else{
            D('Threadpost')->updateCount($post_id,'views');
            $pics = D('Threadpostphoto')->where(array('post_id'=>$post_id))->select();
            $this->assign('pics',$pics);
            if (!$res = D('Threadpostzan')->where(array('create_ip' => get_client_ip(), 'post_id' => $post_id))->find()) {
                $detail['is_zan'] = 0;
            } else {
                $detail['is_zan'] = 1;
            }
            //话题推荐
            $tui_list = D('Threadpost')->where(array('thread_id'=>$detail['thread_id'],'post_id'=>array('NEQ',$post_id)))->order(array('post_id'=>'desc'))->limit(4)->select();
            $this->assign('tui_list',$tui_list);

            $this->assign('auth',D('Users')->find($detail['user_id']));
            $this->assign('tribe',D('Thread')->find($detail['thread_id']));
            $this->assign('nextpage', LinkTo('thread/postload',array('post_id'=>$post_id,'t' => NOW_TIME,'p' => '0000')));
            $this->assign('detail',$detail);
            $this->display();
        }
    }

    public function postload(){
            //回复的帖子
            import('ORG.Util.Page'); // 导入分页类
            $post_id = (int)$this->_param('post_id');
            $reply_list = D('Threadpostcomments')->where(array('post_id'=>$post_id,'type'=>array('IN',array(1,2))))->order(array('comment_id'=>'desc'))->select();
            $user_idss = $comment_idss = array();
            foreach($reply_list as $k=>$val){
                $user_idss[$val['user_id']] = $val['user_id'];
                $comment_idss[$val['comment_id']] = $val['comment_id'];
            }
            $userss = D('Users')->itemsByIds($user_idss);
            $reply_picss =  D('Tribecommentsphoto')->where(array('comment_id'=>array('IN',$comment_idss)))->select();
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
			
			$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
			$p = $_GET[$var];
			if ($Page->totalPages < $p) {
				die('0');
			}
		
		
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
            $this->assign('userss',$userss);
            $this->assign('users', D('Users')->itemsByIds($user_ids));
            $this->assign('list',$list);
            $this->assign('page', $show); 
            $this->display();
    }

    

    public function lists(){
        if($cate_id = (int)$this->_param('cate_id')){
            $this->assign('cate_id',$cate_id);
        }
        $this->assign('nextpage', LinkTo('thread/listsload',array('cate_id'=>$cate_id,'t' => NOW_TIME,'p' => '0000')));
		$this->display();
	}
    
    
    public function listsload(){
        $thread = D('Thread');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0);
        if($cate_id = (int)$this->_param('cate_id')){
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id',$cate_id);
        }
        
        $count = $thread->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $thread->where($map)->order(array('thread_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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

    public function attent(){
        if (empty($this->uid)) {
            $this->error('请先登录后操作！',U('passport/login'));
        }
        $this->assign('nextpage', LinkTo('thread/attentload',array('t' => NOW_TIME,'p' => '0000')));
		$this->display();
	}
    
    
    public function attentload(){
        $threadcollect = D('Threadcollect');
        import('ORG.Util.Page'); 
        $map = array('user_id' => $this->uid);
        $count = $threadcollect->where($map)->count(); 
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $threadcollect->where($map)->order(array('thread_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        $thread_ids = array();
        foreach($list as $k=>$val){
            $thread_ids[$val['thread_id']] = $val['thread_id'];
        }
        $this->assign('tribes',D('Thread')->itemsByIds($thread_ids));
        $this->assign('list', $list); 
        $this->assign('page', $show); 
		$this->display();
    }
    
    public function collect() {
        if (empty($this->uid)) {
            $this->fengmiMsg('请先登录后操作！',U('passport/login'));
        }
        $thread_id = (int) $this->_get('thread_id');
        if (!$detail = D('Thread')->find($thread_id)) {
            $this->fengmiMsg('没有该主题');
        }
        if ($detail['closed']) {
            $this->fengmiMsg('该主题已经被删除');
        }
        if (D('Threadcollect')->check($thread_id, $this->uid)) {
            if(D('Threadcollect')->where(array('thread_id'=>$thread_id,'user_id'=>$this->uid))->delete()){
                D('Thread')->updateCount($thread_id,'fans',-1);
                $this->fengmiMsg('取消关注成功！',U('thread/lists',array('cate_id'=>$detail['cate_id'])));
            }
            $this->fengmiMsg('取消失败！');
        }else{
            $data = array(
                'thread_id' => $thread_id,
                'user_id' => $this->uid,
            );
            if (D('Threadcollect')->add($data)) {
                D('Thread')->updateCount($thread_id,'fans');
                $this->fengmiMsg('恭喜您关注成功！',U('thread/detail',array('thread_id'=>$thread_id)));
            }
            $this->fengmiMsg('关注失败！');
        }
    }
    
    public function detail($thread_id){
        if(!$thread_id = (int)$thread_id){
            $this->error('主题不存在');
        }elseif(!$detail = D('Thread')->find($thread_id)){
            $this->error('主题不存在');
        }elseif($detail['closed'] != 0){
            $this->error('该主题已被删除');
        }else{
            $threadpost = D('Threadpost');
            if($order = (int)$this->_param('order')){
                $this->assign('order',$order);
            }
            $count = $threadpost->where(array('audit'=>1,'closed'=>0,'thread_id'=>$thread_id))->count(); // 查询满足要求的总记录数 
            $this->assign('count',$count);
            if($res = D('Threadcollect')->where(array('thread_id'=>$thread_id,'user_id'=>$this->uid))->find()){
                $detail['collect'] = 1;
            }
            $collect = D('Threadcollect')->where(array('user_id'=>$this->uid))->select();
            $tr_ids = array();
            foreach ($collect as $k=>$val){
                $tr_ids[] = $val['thread_id'];
            }            
            $threads = D('Thread')->where(array('cate_id'=>$detail['cate_id'],'closed'=>0,'thread_id'=>array('NOTIN',$tr_ids)))->limit(3)->select();
            $this->assign('tribes',$threads);
            $this->assign('detail',$detail);
            $this->assign('nextpage', LinkTo('thread/load',array('thread_id'=>$thread_id,'order'=>$order,'t' => NOW_TIME,'p' => '0000')));
            $this->display();
        }
	}
    
    public function load(){
        $threadpost = D('Threadpost');
        import('ORG.Util.Page'); 
        $map = array('closed' => 0,'audit'=>1);
        if($thread_id = (int)$this->_param('thread_id')){
            $map['thread_id'] = $thread_id;
        }
        if($order = (int)$this->_param('order')){
            if($order == 2){
                $orderby = array('post_id'=>'desc');
            }else{
                $orderby = array('last_time'=>'desc');
            }
        }else{
            $orderby = array('last_time'=>'desc');
        }
        $this->assign('order',$order);
        $count = $threadpost->where($map)->count();  
        $this->assign('count',$count);
        $Page = new Page($count, 15); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $threadpost->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
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
        $this->display();
    }

    public function fabu($thread_id){
        if (empty($this->uid)) {
            $this->error('请先登录后操作！',U('passport/login'));
        }
        if(!$thread_id = (int)$thread_id){
            $this->error('主题不存在');
        }elseif(!$detail = D('Thread')->find($thread_id)){
            $this->error('主题不存在');
        }elseif($detail['closed'] != 0){
            $this->error('主题已被删除');
        }else{
            if($this->isPost()){
                $data['title'] = htmlspecialchars($this->_param('title'));
                if(empty($data['title'])){
                    $this->fengmiMsg('标题不能为空');
                }
                if ($words = D('Sensitive')->checkWords($data['title'])) {
                    $this->baoError('标题含有敏感词：' . $words);
                } 
                $data['details'] = htmlspecialchars($this->_param('details'));
                if(empty($data['details'])){
                    $this->fengmiMsg('详情不能为空');
                }
                if ($words2 = D('Sensitive')->checkWords($data['details'])) {
                    $this->fengmiMsg('详情含有敏感词：' . $words2);
                } 
                $photos = $this->_param('photos',false);
                $data['user_id'] = $this->uid;
				$data['city_id'] = $this->city_id;
                $data['cate_id'] = $detail['cate_id'];
                $data['thread_id'] = $thread_id;
                $data['create_time'] = NOW_TIME;
                $data['last_id'] = $this->uid;
                $data['last_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $data['audit'] = $this->_CONFIG['site']['tribeaudit'];
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
                    $this->fengmiMsg('恭喜您发帖成功',U('thread/detail',array('thread_id'=>$thread_id)));
                }else{
                    $this->fengmiMsg('发帖失败');
                }
            }else{
                $this->assign('detail',$detail);
                $this->display();
            }
        }
	}
    
    public function reply($post_id,$comment_id=0){
        if (empty($this->uid)) {
             $this->error('请先登录后操作！',U('passport/login'));
        }
        
        if(!$post_id = (int)$post_id){
            $this->error('该话题不存在');
        }elseif(!$post = D('Threadpost')->find($post_id)){
            $this->error('该话题不存在');
        }elseif($post['audit'] !=1||$post['closed']!=0){
            $this->error('该话题不存在');
        }else{
            if($comment_id = (int)$comment_id){
                if(!$detail = D('Threadpostcomments')->find($comment_id)){
                    $this->error('该评论不存在');
                }elseif($detail['closed']!=0){
                    $this->error('该评论不存在');
                }
            }
            if ($this->isPost()) {
                    $data['contents'] = htmlspecialchars($this->_param('contents'));
                    if(empty($data['contents'])){
                        $this->fengmiMsg('回复内容不能为空');
                    }
                    if ($words = D('Sensitive')->checkWords($data['contents'])) {
                        $this->fengmiMsg('回复内容含有敏感词：' . $words);
                    }
                    if($com_id = (int)$this->_param('comment_id')){
                        
                        $data['reply_comment_id'] = $com_id;
                        $data['type'] = 1;
                    }
                    $data['post_id'] = $post_id;
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
                        D('Threadpost')->updateCount($post_id, 'reply_num');
                        D('Threadpost')->save(array('post_id' => $post_id, 'last_id' => $this->uid, 'last_time' => $data['create_time']));
						D('Users')->prestige($this->uid, 'thread');
                        $this->fengmiMsg('回复成功',U('thread/postdetail',array('post_id'=>$post_id)));
                    }else{
                        $this->fengmiMsg('回复失败');
                    }
            }else{
                $this->assign('comment_id',$comment_id);
                $this->assign('post_id',$post_id);
                $this->display();
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
	public function photo($thread_id){
        if(!$thread_id = (int)$thread_id){
             $this->error('主题不存在');
        }
		$detail = D('Thread')->find($thread_id);
        $map = array('audit'=>1,'closed'=>0,'thread_id'=>$thread_id);
        $thread = D('Threadpost')->where($map)->select();
        if(!$thread){
            $this->error('主题不存在');
        }
        foreach ($thread as $k => $v) {
            if(!$post_ids){
                $post_ids = $v['post_id'];
            }else{
                $post_ids = $post_ids.','.$v['post_id'];
            }
        }
		$lists = D('Threadpostphoto')->getbypost_ids($post_ids);
		import('ORG.Util.Page'); 
        $count = count($lists);
        $Page = new Page($count, 20);
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = array_slice($lists, $Page->firstRow, $Page->listRows);
        $this->assign('photos',$list);
		$this->assign('detail',$detail);
        $this->display();

	}
    

}
