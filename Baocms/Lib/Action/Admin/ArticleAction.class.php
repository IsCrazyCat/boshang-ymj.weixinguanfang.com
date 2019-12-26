<?php
class ArticleAction extends CommonAction{
    private $create_fields = array('cate_id', 'city_id', 'area_id', 'shop_id', 'title', 'source', 'keywords', 'profiles', 'desc', 'photo', 'details', 'create_time', 'create_ip', 'views','audio','video','video_photo', 'orderby', 'istop', 'isroll', 'valuate');
    private $edit_fields = array('cate_id', 'city_id', 'area_id', 'shop_id', 'title', 'source', 'keywords', 'profiles', 'desc', 'photo', 'details', 'views','audio','video', 'video_photo','orderby', 'istop', 'isroll', 'valuate');
    public function index(){
        $Article = D('Article');
        import('ORG.Util.Page');
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($parent_id = (int) $this->_param('parent_id')) {
            $this->assign('parent_id', $parent_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Article->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Article->where($map)->order(array('article_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('cates', D('Articlecate')->fetchAll());
        $this->display();
    }
    public function recovery(){
        $Article = D('Article');
        import('ORG.Util.Page');
        $map = array('closed' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($parent_id = (int) $this->_param('parent_id')) {
            $this->assign('parent_id', $parent_id);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Article->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Article->where($map)->order(array('article_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('cates', D('Articlecate')->fetchAll());
        $this->display();
    }
	//文章打赏列表
	public function donate(){
        $Articledonate = D('Articledonate');
        import('ORG.Util.Page');
        $map = array();
        if ($user_id = (int) $this->_param('user_id')) {
            $users = D('Users')->find($user_id);
            $this->assign('nickname', $users['nickname']);
            $this->assign('user_id', $user_id);
            $map['user_id'] = $user_id;
        }
        $count = $Articledonate->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Articledonate->where($map)->order(array('donate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$user_ids = array();
        foreach ($list as $k => $val) {
			$user_ids[$val['user_id']] = $val['user_id'];
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
		$this->assign('users', D('Users')->itemsByIds($user_ids));
		$this->assign('sum', $sum = $Articledonate->where($map)->sum('money'));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Article');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('article/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Articlecate')->fetchAll());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['source'] = htmlspecialchars($data['source']);
        $data['keywords'] = htmlspecialchars($data['keywords']);
        $data['desc'] = htmlspecialchars($data['desc']);
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['profiles'] = htmlspecialchars($data['profiles']);
        if (empty($data['profiles'])) {
            $this->baoError('简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['profiles'])) {
            $this->baoError('简介内容含有敏感词：' . $words);
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详细内容含有敏感词：' . $words);
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['views'] = (int) $data['views'];
		$data['audio'] = htmlspecialchars($data['audio']);
		$data['video'] = htmlspecialchars($data['video']);
		$data['video_photo'] = htmlspecialchars($data['video_photo']);
        if (!empty($data['video_photo']) && !isImage($data['video_photo'])) {
            $this->baoError('视频封面格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['istop'] = (int) $data['istop'];//小灰灰添加
        $data['isroll'] = (int) $data['isroll']; //小灰灰添加
        $data['valuate'] = (int) $data['valuate']; //小灰灰添加
        return $data;
    }
    public function edit($article_id = 0) {
        if ($article_id = (int) $article_id) {
            $obj = D('Article');
            if (!($detail = $obj->find($article_id))) {
                $this->baoError('请选择要编辑的文章');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
				
                $data['article_id'] = $article_id;
				if(!empty($detail['news_id'])){
					if (false == D('Shopnews')->update_shop_news($detail['news_id'],$data)){
						$this->baoError('更新商家文章失败');
					}
				}
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('article/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('parent_id', D('Articlecate')->getParentsId($detail['cate_id']));
                $this->assign('shops', D('Shop')->find($detail['shop_id']));
                $this->assign('cates', D('Articlecate')->fetchAll());
                $this->assign('citys', D('City')->fetchAll());
                $this->assign('areas', D('Area')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的文章');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
        $data['shop_id'] = (int) $data['shop_id'];
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['source'] = htmlspecialchars($data['source']);
        $data['keywords'] = htmlspecialchars($data['keywords']);
        $data['desc'] = htmlspecialchars($data['desc']);
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->baoError('缩略图格式不正确');
        }
        $data['profiles'] = SecurityEditorHtml($data['profiles']);
        if (empty($data['profiles'])) {
            $this->baoError('简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['profiles'])) {
            $this->baoError('简介内容含有敏感词：' . $words);
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详细内容含有敏感词：' . $words);
        }
        $data['views'] = (int) $data['views'];
		$data['audio'] = htmlspecialchars($data['audio']);
		$data['video'] = htmlspecialchars($data['video']);
		$data['video_photo'] = htmlspecialchars($data['video_photo']);
        if (!empty($data['video_photo']) && !isImage($data['video_photo'])) {
            $this->baoError('视频封面格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['istop'] = (int) $data['istop'];//小灰灰添加
        $data['isroll'] = (int) $data['isroll'];//小灰灰添加
        $data['valuate'] = (int) $data['valuate']; //小灰灰添加
        return $data;
    }
    public function audit($article_id = 0){
        if (is_numeric($article_id) && ($article_id = (int) $article_id)) {
            $obj = D('Article');
            $obj->save(array('article_id' => $article_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('article/index'));
        } else {
            $article_id_id = $this->_post('article_id', false);
            if (is_array($article_id)) {
                $obj = D('Article');
                foreach ($article_id as $id) {
                    $obj->save(array('article_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('article/index'));
            }
            $this->baoError('请选择要审核的文章');
        }
    }
    public function delete($article_id = 0){
        if (is_numeric($article_id) && ($article_id = (int) $article_id)) {
            $obj = D('Article');
            $obj->save(array('article_id' => $article_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('article/index'));
        } else {
            $article_id = $this->_post('article_id', false);
            if (is_array($article_id)) {
                $obj = D('Article');
                foreach ($article_id as $id) {
                    $obj->save(array('article_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('批量删除成功！', U('article/index'));
            }
            $this->baoError('请选择要删除的文章');
        }
    }
    public function recovernews($article_id = 0){
        $obj = D('Article');
        $obj->save(array('article_id' => $article_id, 'closed' => 0));
        $this->baoSuccess('恢复文章成功！', U('article/Recovery'));
    }
    public function deletecompletely($article_id = 0)
    {
        $obj = D('Article');
        $obj->delete($article_id);
        $this->baoSuccess('彻底删除成功1！', U('article/recovery'));
    }
}