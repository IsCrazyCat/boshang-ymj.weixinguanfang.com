<?php
class FansAction extends CommonAction
{
    public function index()
    {
        $fans = D('Shopfavorites');
        //实例化fans模型
        import('ORG.Util.Page');
        // 导入分页类
        $map = array('shop_id' => $this->shop_id);
        //查询条件
        if ($keyword = $this->_post('keyword', 'htmlspecialchars')) {
            $maps['nickname|mobile'] = trim($keyword);
            $Users = D('Users');
            $user = $Users->where($maps)->find();
            if (!empty($user)) {
                $map['user_id'] = $user['user_id'];
            }
            $this->assign('keyword', $keyword);
        }
        $count = $fans->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $fans->where($map)->order(array('favorites_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
        }
        if ($user_ids) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
    }
    public function add($user_id = 0)
    {
        $fans = D('Shopfavorites');
        $uid = (int) $user_id;
        $user = D('Users')->find($user_id);
        $shop = D('shop')->find($this->shop_id);
        if ($this->isPost()) {
            $integral = (int) $_POST['integral'];
            if ($integral <= 0) {
                $this->fengmiMsg('请输入正确的积分');
            }
            if ($this->member['integral'] < $integral) {
                $this->fengmiMsg('您的账户积分不足');
            }
            D('Users')->addIntegral($this->uid, -$integral, '赠送会员积分');
            D('Users')->addIntegral($user_id, $integral, '获得商家赠送积分');
            $this->fengmiMsg('赠送积分成功!', U('fans/index'));
        } else {
            $this->assign('shop', $shop);
            $this->assign('jifen', $this->member['integral']);
            $this->assign('user', $user);
            $this->display();
        }
    }
}