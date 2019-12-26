<?php

class FavoritesAction extends CommonAction {

    public function index() {
        $like = I('like', '', 'trim,htmlspecialchars');
        $this->assign('like', $like);
		$this->assign('nextpage', LinkTo('favorites/loaddata', array("t"=>time(),"p"=>"0000","like"=>$like)));
        $this->display();
    }

    public function loaddata() {
        $like = I('like', '', 'trim,htmlspecialchars');
        $Goodsfavorites = D('Goodsfavorites');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Goodsfavorites->where($map)->count();
        $Page = new Page($count, 10); 
        $show = $Page->show(); 
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
			die('0');
        }
        $list = $Goodsfavorites->where($map)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $goods_ids = array();
        foreach ($list as $k => $val) {
            $goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        $this->assign('goods',D('Goods')->itemsByIds($goods_ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	 public function delete($favorites_id = 0) {
        $favorites_id = (int) $favorites_id;
        $obj = D('Goodsfavorites');
		if (!$detail = $obj->find($favorites_id)){
           $this->error('请选择要删除的商品收藏');
        }
		if ($detail['user_id'] != $this->uid) {
           $this->error('请不要非法操作');
        }
        $obj->delete($favorites_id);
        $this->success('删除成功！', U('favorites/index'));
      
    }
    
}
