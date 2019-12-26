<?php
class ArticleAction extends CommonAction {
	


    public function index() {
        $content_id = (int) $this->_get('content_id');
        if (empty($content_id)) {
            $content_id = 1;
        }
        $contents = D('Systemcontent')->fetchAll();
        if (!$contents[$content_id]) {
            $this->error('该内容不存在');
            die;
        }
        $this->assign('detail', $contents[$content_id]);
        $this->assign('contents', $contents);
        $this->assign('content_id', $content_id);
        $this->seodatas['title'] = $contents[$content_id]['title'];
        $this->display();
    }

    public function detail($article_id = 0) {

        if ($article_id = (int) $article_id) {
            $obj = D('Article');
            if (!$detail = $obj->find($article_id)) {
                $this->error('没有该文章');
            }
            $cates = D('Articlecate')->fetchAll();
            $obj->updateCount($article_id, 'views');
            $this->assign('detail', $detail);

            $this->assign('parent_id', D('Articlecate')->getParentsId($detail['cate_id']));
            $this->assign('cates', $cates);
            $this->assign('cate',$cates[$detail['cate_id']]);
            $this->seodatas['title'] = $detail['title'];
            $this->seodatas['cate_name'] = $cates[$detail['cate_id']];
            $this->seodatas['keywords'] = $detail['keywords'];
            $this->seodatas['desc'] = $detail['desc'];

            $this->display();
        } else {
            $this->error('没有该文章');
        }
    }

    public function system() {
        $content_id = (int) $this->_get('content_id');
        if (empty($content_id)) {
            $this->error('该内容不存在');
            die;
        }
        $contents = D('Systemcontent')->fetchAll();
        if (!$contents[$content_id]) {
            $this->error('该内容不存在');
            die;
        }
        $this->assign('detail', $contents[$content_id]);
        $this->assign('contents', $contents);
        $this->assign('content_id', $content_id);
        $this->seodatas['title'] = $contents[$content_id]['title'];
        $this->display();
    }

}