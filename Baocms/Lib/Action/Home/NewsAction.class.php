<?php
class NewsAction extends CommonAction
{
    public function _initialize()
    {
        parent::_initialize();
        $news = (int) $this->_CONFIG['operation']['news'];
        if ($news == 0) {
            $this->error('此功能已关闭');
            die;
        }
        $cache = cache(array('type' => 'File', 'expire' => 600));
        if (!($counts = $cache->get('index_count'))) {
            $counts['shop'] = D('Shop')->count();
            $counts['coupon'] = D('Coupon')->count();
            $counts['users'] = D('Users')->count();
            $counts['life'] = D('Life')->count();
            $counts['post'] = D('Post')->count();
            $counts['community'] = D('Community')->count();
            $cache->set('index_count', $counts);
        }
        $this->assign('counts', $counts);
    }
    public function index()
    {
        $Article = D('Article');
        import('ORG.Util.Page');// 导入分页类
        $map = array('city_id' => $this->city_id, 'closed' => 0, 'audit' => 1);

        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $cat = (int) $this->_param('cat');
        $cates = D('Articlecate')->fetchAll();
        if ($cates[$cat]) {
            $catids = D('Articlecate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cat;
            }
            $this->assign('parent_id', $cates[$cat]['parent_id'] == 0 ? $cates[$cat]['cate_id'] : $cates[$cat]['parent_id']);
            $this->seodatas['cate_name'] = $cates[$cat]['cate_name'];
        }
        $this->assign('cat', $cat);
        $order = (int) $this->_param('order');
        switch ($order) {
            case 2:
                $orderby = array('views' => 'desc');
                break;
            default:
                $orderby = array('article_id' => 'desc');
                break;
        }
        $count = $Article->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Article->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('cates', $cates);
        $this->display();

    }
    public function detail($article_id = 0)
    {
        if ($article_id = (int) $article_id) {
            $obj = D('Article');
            if (!($detail = $obj->find($article_id))) {
                $this->error('没有该文章');
            }
            if ($detail['closed'] != 0) {
                $this->error('该文章已删除');
            }
            $cates = D('Articlecate')->fetchAll();
			$Article_cate = D('Articlecate')->fetchAll();
			if ($Article_cate[$detail['cate_id']]['parent_id'] == 0) {
				$this->assign('catstr', $Article_cate[$detail['cate_id']]['cate_name']);
			} else {
				$this->assign('catstr', $Article_cate[$Article_cate[$detail['cate_id']]['parent_id']]['cate_name']);
				$this->assign('cat', $Article_cate[$detail['cate_id']]['parent_id']);
				$this->assign('catestr', $Article_cate[$detail['cate_id']]['cate_name']);
			}
		
            $obj->updateCount($article_id, 'views');
            $shop_id = $detail['shop_id'];
            $shop = D('Shop')->find($shop_id);
            $this->assign('shops', $shop);
            $Articlecomment = D('Articlecomment');
            import('ORG.Util.Page');
            $map = array('city_id' => $this->city_id, 'post_id' => $article_id);
            $count_comment = $Articlecomment->where($map)->count();
            $Page_comment = new Page($count_comment, 15);
            $show_comment = $Page_comment->show();
            $this->assign('count', $count);
            $list = $Articlecomment->where($map)->order()->limit($Page_comment->firstRow . ',' . $Page_comment->listRows)->select();
            $user_ids = array();
            foreach ($list as $k => $val) {
                if (!empty($val['user_id'])) {
                    $user_ids[$val['user_id']] = $val['user_id'];
                }
            }
            $this->assign('users', D('Users')->itemsByIds($user_ids));
            $this->assign("list", $list);
            $this->assign('page_comment', $show_comment);
            $Articledonate = D('Articledonate');
            import('ORG.Util.Page');
            $map_donate = array('city_id' => $this->city_id, 'article_id' => $article_id);
            $count_donate = $Articledonate->where($map_donate)->count();
            $Page = new Page($count_donate, 15);
            $show = $Page->show();

            $donate = $Articledonate->where($map_donate)->order()->limit($Page->firstRow . ',' . $Page->listRows)->select();
            $donate_user_ids = array();
            foreach ($donate as $k => $val) {
                if (!empty($val['user_id'])) {
                    $donate_user_ids[$val['user_id']] = $val['user_id'];
                }
            }
            $this->assign('dusers', D('Users')->itemsByIds($donate_user_ids));
            $this->assign('donate', $donate);
            $this->assign('page', $show);
            // 赋值分页输出
            $this->assign('detail', $detail);
            $this->assign('parent_id', D('Articlecate')->getParentsId($detail['cate_id']));
            $this->assign('cates', $cates);
            $this->assign('cate', $cates[$detail['cate_id']]);
            $this->seodatas['title'] = $detail['title'];
            $this->seodatas['cate_name'] = $cates[$detail['cate_id']];
            $this->seodatas['keywords'] = $detail['keywords'];
            if (!empty($detail['desc'])) {
                $this->seodatas['desc'] = $detail['desc'];
            } else {
                $this->seodatas['desc'] = bao_msubstr($detail['details'], 0, 200, false);
            }
            $rands = D("Article")->rands();
            $this->assign('rands', $rands);
            $this->assign('articlecomments', D('Articlecomment')->where(array('post_id' => $article_id))->count());
            //$this->assign('pics', D('Articlephoto')->getPics($detail['life_id']));//调用图片
            $this->display();
        } else {
            $this->error('没有该文章');
        }
    }
    public function system()
    {
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
    public function reply()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $data = $this->checkFields($this->_post('data', false), array('article_id', 'parent_id', 'content'));
        if (empty($data['content'])) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '评论内容不能为空'));
        }
        if ($words = D('Sensitive')->checkWords($content)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '商家介绍含有敏感词：' . $words));
        }
        if (empty($data['article_id'])) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '文章编号不正确'));
        }
        if (!($detail = D('Article')->find($data['article_id']))) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '没有该文章'));
        }
        $data['post_id'] = $data['article_id'];
        $data['nickname'] = $this->member['nickname'];
        $data['user_id'] = $this->uid;
        $data['zan'] = 0;
        $data['audit'] = $this->_CONFIG['site']['article_reply_audit'];
        //评论是免审核
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        if ($comment_id = D('Articlecomment')->add($data)) {
            $photos = $this->_post('photos', false);
            if (!empty($photos)) {
                D('Articlephoto')->upload_comment($comment_id, $photos);
                //更新回复，如果是新闻用其他的
            }
            $this->ajaxReturn(array('status' => 'success', 'msg' => '回复成功！', U('news/detail', array('article_id' => $detail['post_id']))));
        } else {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败'));
        }
    }
    public function zan()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $article_id = I('article_id', 0, 'trim,intval');
        $detail = D('Article')->find($article_id);
        if (empty($detail)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '您点赞的内容不存在'));
        }
        D('Article')->updateCount($article_id, 'zan');
        $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您，点赞成功！', U('news/detail', array('article_id' => $detail['post_id']))));
    }
    public function donate()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'login'));
        }
        $article_id = I('article_id', 0, 'trim,intval');
        $money = I('money', 0, 'trim,intval') * 100;
        $detail = D('Article')->find($article_id);
        if (empty($detail)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '您打赏的内容不存在'));
        }
        if ($money > $this->member['money'] || $this->member['money'] == 0) {
            $this->ajaxReturn(array('status' => 'success', 'msg' => '您余额不足', U('members/money/money')));
        } else {
            if (!empty($money)) {
                $data['article_id'] = $article_id;
                $data['city_id'] = $this->city_id;
                $data['user_id'] = $this->uid;
                $data['money'] = $money;
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                D('Articledonate')->add($data);
                D('Users')->addMoney($this->uid, -$money, '打赏文章ID：' . $article_id . '花费金额');
                D('Article')->updateCount($article_id, 'donate_num');
                $this->ajaxReturn(array('status' => 'success', 'msg' => '恭喜您，打赏成功！', U('news/detail', array('article_id' => $detail['post_id']))));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '操作失败'));
            }
            $this->ajaxReturn(array('status' => 'error', 'msg' => '参数错误'));
        }
    }
    public function cate()
    {
        $cates = D('Articlecate')->fetchAll();
        $Article = D('Article');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('closed' => 0);
        $cat = (int) $this->_param('cat');
        $cates = D('Articlecate')->fetchAll();
        if ($cates[$cat]) {
            $catids = D('Articlecate')->getChildren($cat);
            if (!empty($catids)) {
                $map['cate_id'] = array('IN', $catids);
            } else {
                $map['cate_id'] = $cat;
            }
            $this->assign('parent_id', $cates[$cat]['parent_id'] == 0 ? $cates[$cat]['cate_id'] : $cates[$cat]['parent_id']);
            $this->seodatas['cate_name'] = $cates[$cat]['cate_name'];
        }
        $this->assign('cat', $cat);
        $order = (int) $this->_param('order');
        switch ($order) {
            case 2:
                $orderby = array('views' => 'desc');
                break;
            default:
                $orderby = array('article_id' => 'desc');
                break;
        }
        $this->assign('cate', $cates[$cat]);
        $count = $Article->where($map)->count();
        $Page = new Page($count, 25);
        $show = $Page->show();
        $list = $Article->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('cates', $cates);
        $this->display();
    }
}