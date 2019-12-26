<?php
class WeixinmsgAction extends CommonAction
{
    public function index()
    {
        $Weixinmsg = D('Weixinmsg');
        import('ORG.Util.Page');
        $map = array();
        $count = $Weixinmsg->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Weixinmsg->where($map)->order(array('msg_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
        // 输出模板
    }
}