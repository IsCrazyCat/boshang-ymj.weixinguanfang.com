<?php
class HouseworkAction extends CommonAction
{
    public function _initialize()
    {
        parent::_initialize();
        if ($this->_CONFIG['operation']['lifeservice'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
    }
    public function index()
    {
        $holder = D('shop')->where(array('shop_id' => $this->shop_id))->find();
        $Housework = D('Housework');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['svctime|contents'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Housework->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 3);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Housework->where($map)->order(array('housework_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $workids = array();
        foreach ($list as $k => $val) {
            $workids[$val['housework_id']] = $val['housework_id'];
            if (empty($val['num'])) {
                $list[$k]['num'] = $this->_CONFIG['housework']['num'];
            }
            if (empty($val['gold'])) {
                $list[$k]['gold'] = $this->_CONFIG['housework']['gold'];
            }
        }
        $this->assign('looks', D('Houseworklook')->checkLook($this->shop_id, $workids));
        $this->assign('workTypes', $Housework->getCfg());
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('holder', $holder);
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function look($housework_id)
    {
        if (!($housework_id = (int) $housework_id)) {
            $this->error('参数错误');
        }
        if (!($detail = D('Housework')->find($housework_id))) {
            $this->error('参数错误');
        }
        if (empty($detail['num'])) {
            $detail['num'] = $this->_CONFIG['housework']['num'];
        }
        if (empty($detail['money'])) {
            $detail['money'] = ($this->_CONFIG['housework']['gold'])*100;
        }
        if ($detail['num'] <= $detail['buy_num']) {
            $this->error('该信息已经超过最大查看数了！');
        }
        if (D('Houseworklook')->checkIsLook($this->shop_id, $housework_id)) {
            $this->error('您已经购买过该信息！');
        }
        if (!empty($detail['money'])) {
            if ($this->member['money'] < $detail['money']) {
                $this->error('账户金块余额不足', U('mcenter/money/index'));
            }
            D('Users')->addMoney($this->uid, -$detail['money'], '购买家政服务：电话' . $detail['tel']);
        }
        D('Houseworklook')->add(array(
			'housework_id' => $housework_id, 
			'shop_id' => $this->shop_id, 
			'create_time' => NOW_TIME, 
			'create_ip' => get_client_ip()
		));
        D('Housework')->updateCount($housework_id, 'buy_num');
        $this->error('恭喜您购买查看该服务成功！', U('housework/index'));
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->checkCreate();
            if (D('Housework')->add($data)) {
                $this->error('添加成功', U('Housework/index'));
            } else {
                $this->error('增加失败', U('Housework/index'));
            }
        } else {
            $this->display();
        }
    }
    public function checkCreate()
    {
        $data = $this->checkFields($this->_post('data', false), array('contents', 'phone', 'num', 'addr', 'svctime', 'svc_id', 'name', 'tel', 'gold'));
        $data['shop_id'] = (int) $this->shop_id;
        $data['contents'] = $data['contents'];
        $data['tel'] = htmlspecialchars($data['tel']);
        $data['num'] = (int) $data['num'];
        $data['addr'] = htmlspecialchars($data['addr']);
        $data['svctime'] = htmlspecialchars($data['svctime']);
        $data['svc_id'] = (int) $data['svc_id'];
        $data['name'] = htmlspecialchars($data['name']);
        $data['gold'] = (int) $data['gold'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        if (empty($data['contents'])) {
            $this->error('标题不能为空');
        }
        if (empty($data['svc_id'])) {
            $this->error('类型不能为空');
        }
        if (empty($data['svctime'])) {
            $this->error('有效时间不能为空');
        }
        if (!isMobile($data['tel']) && !isPhone($data['tel'])) {
            $this->error('请输入正确的号码！');
        }
        if (empty($data['addr'])) {
            $this->error('地址不能为空');
        }
        if (empty($data['gold']) || $data['gold'] < 0) {
            $this->error('金块数错误!') . $data['gold'];
        }
        if (empty($data['name'])) {
            $this->error('发布人不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['name'])) {
            $this->error('名称含有敏感词：' . $words);
        }
        return $data;
    }
}