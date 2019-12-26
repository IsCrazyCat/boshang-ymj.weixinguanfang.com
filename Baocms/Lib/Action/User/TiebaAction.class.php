<?php
class TiebaAction extends CommonAction{
    protected function _initialize(){
        parent::_initialize();
		$sharecate = D('Sharecate')->fetchAll();
        $this->assign('sharecate', $sharecate);
        $tieba = (int) $this->_CONFIG['operation']['tieba'];
        if ($tieba == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    public function index(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
        $this->display();
    }
    public function loaddata(){
        $Post = D('Post');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $aready = (int) $this->_param('aready');
        if ($aready == 1) {
            $map['audit'] = 0;
        } elseif ($aready == 0) {
            $map['audit'] = array('IN', array(0, 1));
        } elseif ($aready == 2) {
            $map['audit'] = 1;
        }
        $count = $Post->where($map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Post->where($map)->order(array('post_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $ids = array();
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
                $ids[$val['last_id']] = $val['last_id'];
            }
            $list[$k] = $val;
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 public function edit($post_id=0){
		$post_id = (int) $post_id;
		$obj = D('Post');
		if (!($detail = $obj->find($post_id))) {
           $this->error('请选择要编辑的帖子');
        }
        if ($this->isPost()) {
            $data = $this->editCheck();
            $obj = D('Post');
			
			$data['post_id'] = $post_id;
            $data['city_id'] = $this->city_id;
            $data['audit'] = 0;
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();

            $tupian = $this->_post('data');
            $photos = $this->_post('photos', false);
			// $this->fengmiMsg($photos);
            $photo = $val = '';
            if (!empty($photos)) {
                foreach ($photos as $val) {
                    if (isImage($val) && $val != '') {
						$photo = $photo . '<img src='. config_img($val) .'>';
                    }
                }
            }
            $photo1 = $val1 = '';
            if (!empty($photos)) {
                foreach ($photos as $val1) {
                    if (isImage($val1) && $val1 != '') {
                        $photo1 = $photo1 . ',' . $val1;
                    }
                }
            }
            $data['pic'] = ltrim($photo1, ',');
            $data['details'] = $tupian[contents] . $photo;
            $last = $obj->save($data);
            if ($last) {
                $this->fengmiMsg('修改帖子成功啦！', U('tieba/index'));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->assign('cate', $cate);
			$this->assign('detail', $detail);
			$pic = explode(',',$detail['pic']);
            $this->assign('pic', $pic);
            $this->display();
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), array('cate_id','title', 'contents'));
		$data['cate_id'] = (int) $data['cate_id'];
		if (empty($data['cate_id'])) {
            $this->fengmiMsg('分类不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title']) || $data['title'] == '标题') {
            $this->fengmiMsg('标题不能为空');
        }
        $data['user_id'] = (int) $this->uid;
        $data['contents'] = SecurityEditorHtml($data['contents']);
        if (empty($data['contents'])) {
            $this->fengmiMsg('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['contents'])) {
            $this->fengmiMsg('详细内容含有敏感词：' . $words);
        }
        return $data;
    }

	
    public function delete(){
        $post_id = (int) $this->_param('post_id');
        $obj = D('Post');
        if (empty($post_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '帖子不存在'));
        }
        if (!($detail = D('Post')->find($post_id))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '帖子不存在'));
        }
        if ($detail['user_id'] != $this->uid) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '不要操作别人的帖子'));
        }
        if (D('Post')->delete($post_id)) {
            $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您删除成功'));
        }
    }
}