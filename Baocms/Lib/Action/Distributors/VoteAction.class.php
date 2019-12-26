<?php



class VoteAction extends CommonAction {

    public function index() {
        $Vote = D('Vote');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('shop_id' => $this->shop_id, 'end_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Vote->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Vote->where($map)->order(array('vote_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){
            $url = U('Wap/vote/index',array('vote_id'=>$val['vote_id']));
            $rurl = U('Wap/vote/result',array('vote_id'=>$val['vote_id']));
            $url = __HOST__.$url;
            $rurl = __HOST__.$rurl;
			$list[$k]['url'] = $url;
            $tooken = 'vote_'.$val['vote_id'];
            $file = baoQrCode($tooken,$url);
            $rfile = baoQrCode($tooken,$rurl);
            $list[$k]['file'] = $file;
            $list[$k]['rfile'] = $rfile;
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    

    public function result($vote_id) {

        if (!$detail = D('Vote')->find($vote_id)) {
            $this->error('请选择正确的投票');
        }
        if ($detail['shop_id'] != (int) $this->shop_id) {
            $this->error('请选择正确的投票');
        }
        $total = D('Voteoption')->where(array('vote_id' => $vote_id))->sum('number');
        $this->assign('total', $total);
        $this->assign('options', D('Voteoption')->order(array('orderby' => 'asc'))->where(array('vote_id' => $vote_id))->select());
        $this->assign('detail', $detail);
        $this->display();
    }

    public function work($vote_id = 0) {
        if (is_numeric($vote_id) && ($vote_id = (int) $vote_id)) {
            $obj = D('Vote');
            $detail = D('Voteoption')->where(array('vote_id' => $vote_id))->count();

            $details = $obj->find($vote_id);
            if($details['shop_id'] != $this->shop_id){
                $this->error('请不要试图操作别人的投票');
            }
            $obj->save(array('vote_id' => $vote_id, 'is_work' => 1));
            $this->baoSuccess('启用成功！', U('vote/index'));
        }
        $this->baoError('请选择要启用的投票');
    }

}
