<?php
class TuanAction extends CommonAction
{
    private $create_fields = array('shop_id', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'price', 'tuan_price', 'settlement_price', 'num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook', 'branch_id', 'profit_enable', 'profit_rate1', 'profit_rate2', 'profit_rate3', 'profit_rank_id');
    private $edit_fields = array('shop_id', 'use_integral', 'cate_id', 'intro', 'title', 'photo', 'price', 'tuan_price', 'settlement_price', 'num', 'sold_num', 'bg_date', 'end_date', 'fail_date', 'is_hot', 'is_new', 'is_chose', 'freebook', 'branch_id', 'profit_enable', 'profit_rate1', 'profit_rate2', 'profit_rate3', 'profit_rank_id');
    protected $tuancates = array();
    public function _initialize(){
        parent::_initialize();
        $this->tuancates = D('Tuancate')->fetchAll();
        $this->assign('tuancates', $this->tuancates);
        $branch = D('Shopbranch')->where(array('shop_id' => $this->shop_id, 'closed' => 0, 'audit' => 1))->select();
        $this->assign('branch', $branch);
    }
    public function index(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 0, 'end_date' => array('EGT', TODAY));
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Tuan->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val = $Tuan->_format($val);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //上单添加开始
    public function create(){
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Tuan');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if (empty($details)) {
                $this->fengmiMsg('套餐详情不能为空');
            }
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->fengmiMsg('详细内容含有敏感词：' . $words);
            }
            $instructions = $this->_post('instructions', 'SecurityEditorHtml');
            if (empty($instructions)) {
                $this->fengmiMsg('购买须知不能为空');
            }
            if ($words = D('Sensitive')->checkWords($instructions)) {
                $this->fengmiMsg('购买须知含有敏感词：' . $words);
            }
            $thumb = $this->_param('thumb', false);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            $data['thumb'] = serialize($thumb);
            if ($tuan_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                //套餐类型是2
                $obj->save(array('tuan_id' => $tuan_id, 'wei_pic' => $wei_pic));
                D('Tuandetails')->add(array('tuan_id' => $tuan_id, 'details' => $details, 'instructions' => $instructions));
                $this->fengmiMsg('添加成功，请等待网站管理员审核后即可显示', U('tuan/index'));
            }
            $this->error('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('套餐分类不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        $data['branch_id'] = (int) $data['branch_id'];
        if (!empty($data['branch_id'])) {
            $branch = D('Shopbranch')->where(array('shop_id' => $this->shop_id, 'branch_id' => $data['branch_id'], 'audit' => 1))->find();
            $data['lng'] = $branch['lng'];
            $data['lat'] = $branch['lat'];
            $data['area_id'] = $branch['area_id'];
            $data['business_id'] = $branch['business_id'];
            $data['city_id'] = $branch['city_id'];
        } else {
            $data['lng'] = $this->shop['lng'];
            $data['lat'] = $this->shop['lat'];
            $data['area_id'] = $this->shop['area_id'];
            $data['business_id'] = $this->shop['business_id'];
            $data['city_id'] = $this->shop['city_id'];
        }
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg('套餐名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->fengmiMsg('套餐副标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传图片');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('图片格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->fengmiMsg('市场价格不能为空');
        }
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if (empty($data['tuan_price'])) {
            $this->fengmiMsg('套餐价格不能为空');
        }
        $data['settlement_price'] = (int) ($data['tuan_price'] - $data['tuan_price'] * $this->tuancates[$data['cate_id']]['rate'] / 1000);
        $data['use_integral'] = (int) $data['use_integral'];
        //套餐检测积分合法性开始
		if (D('Tuan')->check_add_use_integral($data['use_integral'],$data['settlement_price'])) {//传2参数
            //这里暂时保留，后期增加逻辑;
        }else{
			$this->fengmiMsg(D('Tuan')->getError(), 3000, true);	  
		}
		//套餐检测积分合法性结束
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->fengmiMsg('库存不能为空');
        }
        $data['sold_num'] = (int) $data['sold_num'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->fengmiMsg('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->fengmiMsg('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->fengmiMsg('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->fengmiMsg('结束时间格式不正确');
        }
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_new'] = (int) $data['is_new'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['is_multi'] = (int) $data['is_multi'];
        $data['freebook'] = (int) $data['freebook'];
        $data['is_return_cash'] = (int) $data['is_return_cash'];
        $data['fail_date'] = htmlspecialchars($data['fail_date']);
        //增加分销
        $data['profit_enable'] = (int) $data['profit_enable'];
        $data['profit_rate1'] = (int) $data['profit_rate1'];
        $data['profit_rate2'] = (int) $data['profit_rate2'];
        $data['profit_rate3'] = (int) $data['profit_rate3'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($tuan_id = 0){
        if ($tuan_id = (int) $tuan_id) {
            $obj = D('Tuan');
            if (!($detail = $obj->find($tuan_id))) {
                $this->error('请选择要编辑的套餐');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要操作别人的套餐');
            }
            if ($detail['closed'] != 0) {
                $this->error('该套餐已被删除');
            }
            $tuan_details = D('Tuandetails')->getDetail($tuan_id);
            if ($this->isPost()) {
                $data = $this->editCheck();
				//二开图片开始
				$thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['thumb'] = serialize($thumb);
				//二开图片结束
				
                $data['tuan_id'] = $tuan_id;
                if (!empty($detail['wei_pic'])) {
                    if (true !== strpos($detail['wei_pic'], "https://mp.weixin.qq.com/")) {
                        $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                        $data['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($tuan_id, 2);
                    $data['wei_pic'] = $wei_pic;
                }
                $data['audit'] = 0;
                if ($data['tuan_price'] <= $detail['settlement_price']) {
                    $this->fengmiMsg('售价不能小于或者等于结算价格');
                }
                if (false !== $obj->save($data)) {
                    $this->fengmiMsg('操作成功', U('tuan/index'));
                }
                $this->fengmiMsg('操作失败');
            } else {
                $this->assign('detail', $obj->_format($detail));
				$thumb = unserialize($detail['thumb']);
                $this->assign('thumb', $thumb);
				$this->assign('parent_id',D('tuancate')->getParentsId($detail['cate_id']));
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('tuan_details', $tuan_details);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的套餐');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('套餐分类不能为空');
        }
        $data['shop_id'] = $this->shop_id;
        $data['lng'] = $this->shop['lng'];
        $data['lat'] = $this->shop['lat'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg('商品名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->fengmiMsg('副标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传图片');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('图片格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->fengmiMsg('市场价格不能为空');
        }
        $data['tuan_price'] = (int) ($data['tuan_price'] * 100);
        if (empty($data['tuan_price'])) {
            $this->fengmiMsg('套餐价格不能为空');
        }
        $data['settlement_price'] = (int) ($data['tuan_price'] - $data['tuan_price'] * $this->tuancates[$data['cate_id']]['rate'] / 1000);
        $data['use_integral'] = (int) $data['use_integral'];
        //套餐检测积分合法性开始
		if (D('Tuan')->check_add_use_integral($data['use_integral'],$data['settlement_price'])) {//传2参数
            //这里暂时保留，后期增加逻辑;
        }else{
			$this->fengmiMsg(D('Tuan')->getError(), 3000, true);	  
		}
		//套餐检测积分合法性结束
        $data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->fengmiMsg('库存不能为空');
        }
        $data['sold_num'] = (int) $data['sold_num'];
        $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->fengmiMsg('开始时间不能为空');
        }
        if (!isDate($data['bg_date'])) {
            $this->fengmiMsg('开始时间格式不正确');
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->fengmiMsg('结束时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->fengmiMsg('结束时间格式不正确');
        }
        $data['branch_id'] = (int) $data['branch_id'];
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_new'] = (int) $data['is_new'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['is_multi'] = (int) $data['is_multi'];
        $data['freebook'] = (int) $data['freebook'];
        $data['is_return_cash'] = (int) $data['is_return_cash'];
        $data['fail_date'] = htmlspecialchars($data['fail_date']);
        //增加分销
        $data['profit_enable'] = (int) $data['profit_enable'];
        $data['profit_rate1'] = (int) $data['profit_rate1'];
        $data['profit_rate2'] = (int) $data['profit_rate2'];
        $data['profit_rate3'] = (int) $data['profit_rate3'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }
    //上单添加编辑结束
    public function history(){
        $Tuan = D('Tuan');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'closed' => 1);
        $count = $Tuan->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $Tuan->where($map)->order(array('tuan_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val = $Tuan->_format($val);
            $list[$k] = $val;
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function order(){
        $aready = (int) $this->_param('aready');
        $this->assign('aready', $aready);
		$keyword = (int) $this->_param('keyword');
        $this->assign('keyword', $keyword);
        $this->assign('nextpage', linkto('tuan/loaddata', array('aready'=>$aready,'keyword'=>$keyword,'t' => NOW_TIME, 'p' => '0000')));
        $this->display();
    }
	
	public function loaddata(){
        $Tuanorder = D('Tuanorder');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('shop_id' => $this->shop_id);
        
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $keyword = intval($keyword);
            if (!empty($keyword)) {
                $map['order_id'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
        }
        if (isset($_GET['aready']) || isset($_POST['aready'])) {
			$aready = (int) $this->_param('aready');
			if ($aready != 999) {
				$map['status'] = $aready;
			}
			$this->assign('aready', $aready);
		} else {
			$map['status'] = 0;
			$this->assign('aready', 999);
		}
        $count = $Tuanorder->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Tuanorder->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = $user_ids = $tuan_ids = array();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $user_ids[$val['user_id']] = $val['user_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuan', D('Tuan')->itemsByIds($tuan_ids));

        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    public function usedok(){
        $Tuancode = D('Tuancode');
        import('ORG.Util.Page');
        $map = array('shop_id' => $this->shop_id, 'is_used' => '1');
        if (strtotime($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && strtotime($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            if (!empty($bg_time) && !empty($end_date)) {
                $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            }
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                if (!empty($bg_time)) {
                    $map['create_time'] = array('EGT', $bg_time);
                }
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                if (!empty($end_time)) {
                    $map['create_time'] = array('ELT', $end_time);
                }
                $this->assign('end_date', $end_date);
            }
        }
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $keyword = intval($keyword);
            if (!empty($keyword)) {
                $map['code'] = array('LIKE', '%' . $keyword . '%');
                $this->assign('keyword', $keyword);
            }
        }
        $count = $Tuancode->where($map)->count();
        $Page = new Page($count, 20);
        $show = $Page->show();
        $list = $Tuancode->where($map)->order(array('used_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if (!empty($val['shop_id'])) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
            $user_ids[$val['user_id']] = $val['worker_id'];
            $tuan_ids[$val['tuan_id']] = $val['tuan_id'];
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->assign('users', D('Users')->itemsByIds($user_ids));
        $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        $this->assign('tuans', D('Tuan')->itemsByIds($tuan_ids));
        $this->display();
    }
    public function delete($tuan_id = 0)
    {
        $tuan_id = (int) $tuan_id;
        $obj = D('Tuan');
        if (empty($tuan_id)) {
            $this->error('该套餐信息不存在！');
        }
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('该套餐信息不存在！');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('非法操作');
        }
        $obj->save(array('tuan_id' => $tuan_id, 'closed' => 1));
        $this->success('下单成功！', U('tuan/index'));
    }
    public function shelves($tuan_id = 0)
    {
        $tuan_id = (int) $tuan_id;
        $obj = D('Tuan');
        if (empty($tuan_id)) {
            $this->error('该套餐信息不存在！');
        }
        if (!($detail = D('Tuan')->find($tuan_id))) {
            $this->error('该套餐信息不存在！');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('非法操作');
        }
        $obj->save(array('tuan_id' => $tuan_id, 'closed' => 0));
        $this->success('上单成功！', U('tuan/index'));
    }
    //套餐分类
    public function child($parent_id = 0)
    {
        $datas = D('Tuancate')->fetchAll();
        $str = '';
        foreach ($datas as $var) {
            if ($var['parent_id'] == 0 && $var['cate_id'] == $parent_id) {
                foreach ($datas as $var2) {
                    if ($var2['parent_id'] == $var['cate_id']) {
                        $str .= '<option value="' . $var2['cate_id'] . '">' . $var2['cate_name'] . '</option>' . "\n\r";
                        foreach ($datas as $var3) {
                            if ($var3['parent_id'] == $var2['cate_id']) {
                                $str .= '<option value="' . $var3['cate_id'] . '">  --' . $var3['cate_name'] . '</option>' . "\n\r";
                            }
                        }
                    }
                }
            }
        }
        echo $str;
        die;
    }
    public function detail($order_id)
    {
        $order_id = (int) $order_id;
        if (empty($order_id) || !($detail = D('Tuanorder')->find($order_id))) {
            $this->error('该订单不存在');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->error('请不要操作他人的订单');
        }
        if (!($dianping = D('Tuandianping')->where(array('order_id' => $order_id, 'user_id' => $this->uid))->find())) {
            $detail['dianping'] = 0;
        } else {
            $detail['dianping'] = 1;
        }
        $this->assign('tuans', D('Tuan')->find($detail['tuan_id']));
        $this->assign('detail', $detail);
        $this->display();
    }
    public function used()
    {
        $counts['tuan_order_code_is_used'] = (int) D('Tuancode')->where(array('shop_id' => $this->shop_id, 'is_used' => 0))->count();
        //未验证
        if ($this->isPost()) {
            $code = $this->_post('code', false);
            $c = 0;
            foreach ($code as $k => $v) {
                if ($v) {
                    $c = $c + 1;
                }
            }
            if (empty($c)) {
                $this->fengmiMsg('请输入套餐码!');
            }
            $obj = D('Tuancode');
            $shopmoney = D('Shopmoney');
            $return = array();
            $ip = get_client_ip();
            if (count($code) > 10) {
                $this->fengmiMsg('一次最多验证10条套餐码！');
            }
            $userobj = D('Users');
            foreach ($code as $key => $var) {
                $var = trim(htmlspecialchars($var));
                if (!empty($var)) {
                    $data = $obj->find(array('where' => array('code' => $var)));
                    if (!empty($data) && $data['shop_id'] == $this->shop_id && (int) $data['is_used'] == 0 && (int) $data['status'] == 0) {
                        //解决了多多份套餐无法点评的BUG
                        $Tuancode_count = $obj->where(array('order_id' => $data['order_id'], 'is_used' => 0))->count();
                        if ($Tuancode_count == 1) {
                            D('Tuanorder')->save(array('order_id' => $data['order_id'], 'status' => 8));
                            //套餐状态修改为8
                        }
                        if ($obj->save(array('code_id' => $data['code_id'], 'is_used' => 1, 'used_time' => NOW_TIME, 'worker_id' => $this->uid, 'used_ip' => $ip))) {
							 D('Sms')->tuan_TZ_user($data['code_id']);//发短信，先发再处理逻辑
                            //增加MONEY 的过程 稍后补充
                            if (!empty($data['price'])) {
                                $data['intro'] = '套餐消费' . $data['order_id'];
                                $shop = D('Shop')->find($data['shop_id']);
                                $shopmoney->add(array(
									'shop_id' => $data['shop_id'], 
									'city_id' => $shop['city_id'], 
									'area_id' => $shop['area_id'], 
									'branch_id' => $data['branch_id'], 
									'money' => $data['settlement_price'], 
									'create_ip' => $ip, 
									'create_time' => NOW_TIME, 
									'order_id' => $data['order_id'], 
									'intro' => $data['intro']
								));
                                D('Users')->Money($shop['user_id'], $data['settlement_price'], '商户套餐资金结算:' . $data['order_id']);//商户资金增加
                                $return[$var] = $var;
                                D('Users')->gouwu($data['user_id'], $data['price'], '套餐码消费成功');
								//套餐返还积分给商家用户
								if(!empty($data['real_integral'])){
									$config = D('Setting')->fetchAll();
									if($config['integral']['tuan_return_integral'] == 1){
										D('Users')->return_integral($shop['user_id'], $data['real_integral'] , '套餐用户消费积分返还给商家');
									}
								}
                                $this->fengmiMsg($key . '验证成功!', U('tuan/used'));
                            } else {
                                $this->fengmiMsg($key . '到店付套餐码验证成功!', U('tuan/used'));
                            }
                        }
                    } else {
                        $this->fengmiMsg($key . 'X该套餐码无效!', U('tuan/used'));
                    }
                }
            }
        } else {
            $this->assign('counts', $counts);
            $this->display();
        }
    }
}