<?php
class NewsAction extends CommonAction{
    private $edit_fields = array('title','cate_id','city_id', 'area_id','photo','shop_id', 'source','keywords', 'keywords','profiles','details');
	
	 public function _initialize(){
        parent::_initialize();
        $this->articlecate = D('Articlecate')->fetchAll();
        $this->assign('articlecate', $this->articlecate);

    }
	
	
    public function index(){
        $Article = D('Article');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Article->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Article->where($map)->order(array('article_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('cates', D('Articlecate')->fetchAll());
        $this->display();
    }
    public function create(){
        if ($this->isPost()) {
            $data = $this->editCheck();
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            $articles = array(
				'shop_id' => $this->shop_id, 
				'cate_id' => $data['cate_id'], 
				'city_id' => $data['city_id'], 
				'area_id' => $data['area_id'], 
				'source' => $data['source'], 
				'title' => $data['title'], 
				'keywords' => $data['keywords'], 
				'profiles' => $data['profiles'], 
				'photo' => $data['photo'], 
				'details' => $data['details'], 
				'audit' => 0, 
				'create_time' => NOW_TIME, 
				'create_ip' => get_client_ip()
			);
            $articles['article_id'] = D('Article')->add($articles);
            $obj = D('Shopnews');
            if ($news_id = $obj->add($data)) {
                D('Shopfavorites')->save(array('last_news_id' => $news_id), array('where' => array('shop_id' => $this->shop_id)));
                $this->fengmiMsg('添加成功', U('news/index'));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->assign('cates', D('Articlecate')->fetchAll());
            $this->display();
        }
    }
    public function edit($article_id = 0) {
        if (empty($article_id)) {
            $this->error('请选择需要编辑的内容操作');
        }
        $article_id = (int) $article_id;
        $obj = D('Article');
        $detail = $obj->find($article_id);
        if (empty($detail) || $detail['shop_id'] != $this->shop_id) {
            $this->error('请选择需要编辑的内容操作');
        }
        if ($this->isPost()) {
            $data = $this->editCheck();
            $data['article_id'] = $article_id;
			
			if (false == D('Shopnews')->update_shop_news($detail['news_id'],$data)){
                $this->fengmiMsg('更新商家文章失败');
            }
				
            if (false !== $obj->save($data)) {
                $this->fengmiMsg('更新文章成功', U('news/index'));
            }
            $this->fengmiMsg('操作失败');
        } else {
            $this->assign('cates', D('Articlecate')->fetchAll());
            $this->assign('detail', $detail);
			$this->assign('parent_id',D('Articlecate')->getParentsId($detail['cate_id']));
            $this->display();
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $shop = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        $data['shop_id'] = $this->shop_id;
        $data['city_id'] = $shop['city_id'];
        $data['area_id'] = $shop['area_id'];
        $data['source'] = $shop['shop_name'];
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('分类不能为空');
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg('标题不能为空');
        }
        $data['keywords'] = htmlspecialchars($data['keywords']);
        $data['profiles'] = SecurityEditorHtml($data['profiles']);
        if (empty($data['profiles'])) {
            $this->fengmiMsg('简介不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['profiles'])) {
            $this->fengmiMsg('简介内容含有敏感词：' . $words);
        }
		$data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->fengmiMsg('正文不能为空1');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('正文含有敏感词：' . $words);
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!empty($data['photo']) && !isImage($data['photo'])) {
            $this->fengmiMsg('缩略图格式不正确');
        }
        return $data;
    }
    public function delete($article_id = 0){
        if (empty($article_id)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
        }
        $worker = D('Article')->find($article_id);
        if (empty($worker)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
        }
        if ($worker['shop_id'] != $this->shop_id) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '您没有权限访问！'));
        }
        $obj = D('Article');
        $obj->save(array('article_id' => $article_id, 'closed' => 1));
        $this->ajaxReturn(array('status' => 'success', 'msg' => '文章删除成功', U('worker/index')));
    }
    public function child($parent_id = 0)
    {
        $datas = D('Articlecate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['cate_id']) {
                                $str .= '<option value="' . $var3['cate_id'] . '">&nbsp;&nbsp;--' . $var3['cate_name'] . '</option>' . "\n\r";
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
}