<?php
class DistributionAction extends CommonAction
{
    public function _initialize()
    {
        parent::_initialize();
        $profit = $this->_CONFIG['profit']['profit'];
        //赋值分销开关
        if ($profit == 0) {
            $this->error('暂无此功能');
            die;
        }
        $profit_min_rank_id = (int) $this->_CONFIG['profit']['profit_min_rank_id'];
        $fuser = $this->member;
        if ($fuser) {
            $flag = false;
            if ($profit_min_rank_id) {
                $modelRank = D('Userrank');
                $rank = $modelRank->find($profit_min_rank_id);
                $userRank = $modelRank->find($fuser['rank_id']);
                if ($rank) {
                    if ($userRank && $userRank['prestige'] >= $rank['prestige']) {
                        $flag = true;
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
            } else {
                $flag = true;
            }
            if (!$flag) {
                $this->error('对不起您必须达到' . $rank['rank_name'] . '及以上等级才有分销权限');
            }
        }
    }
    public function profit()
    {
        $status = (int) $this->_param('status');
        if (!in_array($status, array(0, 1, 2, 3))) {
            $status = 1;
        }
        $model = D('Userprofitlogs');
        import('ORG.Util.Page');
        // 导入分页类 
        //初始数据
        $map = array('user_id' => $this->uid, 'is_separate' => $status);
        $count = $model->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 8);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        $orderby = array('log_id' => 'DESC');
        $list = $model->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('status', $status);
        $this->display();
    }
    public function subordinate()
    {
        $level = (int) $this->_param('level');
        if (!in_array($level, array(1, 2, 3))) {
            $level = 1;
        }
        $user = D('Users');
        import('ORG.Util.Page');
        // 导入分页类 
        //初始数据
        $map = array('closed' => 0, 'fuid' . $level => $this->uid);
        $count = $user->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 8);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        $orderby = array('user_id' => 'DESC');
        $list = $user->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('level', $level);
        $this->display();
    }
    public function qrcode()
    {
        $token = 'fuid_' . $this->uid;
        //$url = $this->_CONFIG['site']['host'] ? $this->_CONFIG['site']['host'] : __HOST__;
        $url = U('Wap/passport/register', array('fuid' => $this->uid));
        $file = baoQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('host', __HOST__);
        $this->display();
    }
    public function poster()
    {
        $token = 'fuid_' . $this->uid;
        //$url = $this->_CONFIG['site']['host'] ? $this->_CONFIG['site']['host'] : __HOST__;
        $url = U('Wap/passport/register', array('fuid' => $this->uid));
        $file = baoQrCode($token, $url);
        $this->assign('file', $file);
        $this->display();
    }
    public function superior()
    {
        $user = D('Users');
        if ($this->member['fuid1']) {
            $fuser = $user->find($this->member['fuid1']);

        }
        $this->assign('fuser', $fuser);
        $this->display();
    }
}