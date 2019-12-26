<?php
class SellerAction extends CommonAction
{
    public function index()
    {
        $Shopnews = D('Shopnews');
        import('ORG.Util.Pageabc');
        // 导入分页类 
        $map = array('audit' => 1);
        $cates = D('Shopcate')->fetchAll();
        $cat = (int) $this->_param('cat');
        if ($cates[$cat]) {
            $catids = D('Shopcate')->getChildren($cat);
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
            case 3:
                $orderby = array('news_id' => 'desc');
                break;
            case 2:
                $orderby = array('views' => 'desc');
                break;
            default:
                $orderby = array('orderby' => 'asc', 'news_id' => 'desc');
                break;
        }
        $count = $Shopnews->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Shopnews->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('order', $order);
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('cates', $cates);
        $this->display();
        // 输出模板
    }
    public function detail()
    {
        $news_id = (int) $this->_get('news_id');
        if (empty($news_id)) {
            $this->error('请访问正常的内容!');
            die;
        }
        if (!($detail = D('Shopnews')->find($news_id))) {
            $this->error('请访问正常的内容!');
            die;
        }
        if (!$detail['audit']) {
            $this->error('该文章正在审核中!');
            die;
        }
        $cates = D('Shopcate')->fetchAll();
        $this->assign('cate', $cates[$detail['cate_id']]);
        $this->assign('shop', D('Shop')->find($detail['shop_id']));
        //回复列表
        import('ORG.Util.Pageabc');
        // 导入分页类 
        $count = D('Shopcomment')->where(array('post_id' => $news_id, 'parent_id' => 0))->count();
        //获取评论总数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $this->assign('count', $count);
        $list = array();
        $list = $this->getCommlist($news_id, 0, $Page->firstRow, $Page->listRows);
        //获取评论列表
        $this->assign("list", $list);
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('cates', $cates);
        $this->assign('detail', $detail);
        //$this->assign('domain',D('Shopdomain')->domain($detail['shop_id']));
        D('Shopnews')->updateCount($news_id, 'views');
        $this->seodatas['title'] = $detail['title'];
        $this->seodatas['desc'] = niuMsubstr($detail['details'], 0, 200, false);
        $this->display();
    }
    public function zans()
    {
        $comment_id = (int) $this->_get('comment_id');
        $detail = D('Shopcomment')->find($comment_id);
        if (empty($detail)) {
            $this->fengmiError('您点赞的内容不存在！');
        }
        D('Shopcomment')->updateCount($comment_id, 'zan');
        $this->fengmiSuccess('恭喜您，点赞成功！', U('seller/detail', array('news_id' => $detail['post_id'])));
    }
    public function post()
    {
        if (empty($this->uid)) {
            $this->ajaxLogin();
            //提示异步登录
        }
        $data = $this->checkFields($this->_post('data', false), array('post_id', 'parent_id', 'content'));
        if (empty($data['content'])) {
            $this->fengmiError('评论内容不能为空');
        }
        if (empty($data['post_id'])) {
            $this->fengmiError('文章编号不正确');
        }
        if (!($detail = D('Shopnews')->find($data['post_id']))) {
            $this->error('没有该文章');
        }
        $data['nickname'] = $this->member['nickname'];
        $data['user_id'] = $this->uid;
        $data['zan'] = 0;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        if (D('Shopcomment')->add($data)) {
            $this->fengmiSuccess('回复成功！', U('seller/detail', array('news_id' => $data['post_id'])));
        }
    }
    /**
     *递归获取评论列表
     */
    protected function getCommlist($post_id, $parent_id = 0, $start, $end, &$result = array())
    {
        $map = array();
        $map['post_id'] = $post_id;
        $map['parent_id'] = $parent_id;
        if ($parent_id != 0) {
            $arr = D('Shopcomment')->where($map)->order("zan desc")->select();
        } else {
            $arr = D('Shopcomment')->where($map)->order("zan desc")->limit($start . ',' . $end)->select();
        }
        if (empty($arr)) {
            return array();
        }
        foreach ($arr as $cm) {
            $thisArr =& $result[];
            $cm["children"] = $this->getCommlist($cm["post_id"], $cm["comment_id"], $thisArr);
            $thisArr = $cm;
        }
        return $result;
    }
}