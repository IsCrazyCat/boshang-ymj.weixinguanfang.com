<?php
class ShopAction extends CommonAction{
    public function index(){
        $this->display();
    }
    public function logo(){
        if ($this->isPost()) {
            $logo = $this->_post('logo', 'htmlspecialchars');
            if (empty($logo)) {
                $this->baoError('请上传商铺LOGO');
            }
            if (!isImage($logo)) {
                $this->baoError('商铺LOGO格式不正确');
            }
            $data = array('shop_id' => $this->shop_id, 'logo' => $logo);
            if (D('Shop')->save($data)) {
                $this->baoSuccess('上传LOGO成功！', U('shop/logo'));
            }
            $this->baoError('更新LOGO失败');
        } else {
            $this->display();
        }
    }
    public function image(){
        if ($this->isPost()) {
            $photo = $this->_post('photo', 'htmlspecialchars');
            if (empty($photo)) {
                $this->baoError('请上传商铺形象照');
            }
            if (!isImage($photo)) {
                $this->baoError('商铺形象照格式不正确');
            }
			
			$logo = $this->_post('logo', 'htmlspecialchars');
            if (empty($logo)) {
                $this->baoError('请上传商铺LOGO');
            }
            if (!isImage($logo)) {
                $this->baoError('LOGO格式不正确');
            }
			
            $data = array('shop_id' => $this->shop_id, 'photo' => $photo, 'logo' => $logo);
            if (false !== D('Shop')->save($data)) {
                $this->baoSuccess('上传成功', U('shop/image'));
            }
            $this->baoError('更新形象照失败');
        } else {
            $this->display();
        }
    }
    public function about(){
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('addr', 'contact','tel','mobile', 'qq', 'business_time', 'delivery_time'));
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->baoError('店铺地址不能为空');
            }
            $data['contact'] = htmlspecialchars($data['contact']);
			$data['tel'] = htmlspecialchars($data['tel']);
			$data['mobile'] = htmlspecialchars($data['mobile']);
            if (empty($data['mobile'])) {
                $this->baoError('手机不能为空');
            }
            if (!isMobile($data['mobile'])) {
                $this->baoError('手机格式不正确');
            }
            $data['qq'] = htmlspecialchars($data['qq']);
            $data['business_time'] = htmlspecialchars($data['business_time']);
            $data['shop_id'] = $this->shop_id;
            $data['delivery_time'] = (int) $data['delivery_time'];
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('商家介绍含有敏感词：' . $words);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'business_time' => $data['business_time'], 'delivery_time' => $data['delivery_time']);
            unset($data['business_time'], $data['near'], $data['delivery_time']);
            if (false !== D('Shop')->save($data)) {
                D('Shopdetails')->upDetails($this->shop_id, $ex);
                $this->baoSuccess('操作成功', U('shop/about'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('ex', D('Shopdetails')->find($this->shop_id));
            $this->display();
        }
    }
    //其他设置
    public function service(){
        $obj = D('Shop');
        if (!($detail = $obj->find($this->shop_id))) {
            $this->baoError('请选择要编辑的商家');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->baoError('请不要非法操作');
        }
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('is_ele_print','is_tuan_print','is_goods_print','is_booking_print','is_appoint_print','panorama_url','apiKey', 'mKey', 'partner', 'machine_code', 'service'));
			$data['is_ele_print'] = (int) $_POST['is_ele_print'];
			$data['is_tuan_print'] = (int) $_POST['is_tuan_print'];
			$data['is_goods_print'] = (int) $_POST['is_goods_print'];
			$data['is_booking_print'] = (int) $_POST['is_booking_print'];
			$data['is_appoint_print'] = (int) $_POST['is_appoint_print'];
			$data['panorama_url'] = htmlspecialchars($data['panorama_url']);
            $data['apiKey'] = htmlspecialchars($data['apiKey']);
            $data['mKey'] = htmlspecialchars($data['mKey']);
            $data['partner'] = htmlspecialchars($data['partner']);
            $data['machine_code'] = htmlspecialchars($data['machine_code']);
            $data['service'] = $data['service'];
            $data['shop_id'] = $this->shop_id;

            if (false !== $obj->save($data)) {
                $this->baoSuccess('更新成功', U('shop/service'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    //购买短信
    public function sms() {
        $sms_shop_money = $this->_CONFIG['sms_shop']['sms_shop_money']; //单价
        $sms_shop_small = $this->_CONFIG['sms_shop']['sms_shop_small'];//最少购买多少条
        $sms_shop_big = $this->_CONFIG['sms_shop']['sms_shop_big'];//最大购买多少条
        $nums = D('Smsshop')->where(array('type' => shop, 'shop_id' => $this->shop_id))->find();
        if (IS_POST) {
            $num = (int) $_POST['num'];
            if ($num <= 0) {
                $this->baoError('购买数量不合法');
            }
            if ($num % 100 != 0) {
                $this->baoError('总需人次必须为100的倍数');
            }
            if ($num < $sms_shop_small) {
                $this->baoError('购买短信数量不得小于' . $sms_shop_small . '条');
            }
            if ($num > $sms_shop_big) {
                $this->baoError('购买短信数量不得大于' . $sms_shop_big . '条');
            }
            if ($nums['num'] >= 1000) {
                $this->baoError('您当前还有' . $nums['num'] . '条短信，用完再来买吧');
            }
            $money = $num * ($sms_shop_money * 100);
            //总金额
            if ($money > $this->member['money'] || $this->member['money'] == 0) {
                $this->baoError('你的余额不足，请先充值');
            }
            if (D('Users')->addMoney($this->uid, -$money, '商户购买短信：' . $num . '条')) {
                if (empty($nums)) {
                    //如果以前没有购买过
                    $data['user_id'] = $this->uid;
                    $data['shop_id'] = $this->shop_id;
                    $data['type'] = shop;
                    $data['num'] = $num;
                    $data['create_time'] = NOW_TIME;
                    $data['create_ip'] = get_client_ip();
                    D('Smsshop')->add($data);
                } else {
                    D('Smsshop')->where(array('log_id' => $nums['log_id']))->setInc('num', $num);
                    // 增加短信
                }
                $this->baoSuccess('购买短信成功', U('shop/sms'));
            } else {
                $this->baoError('购买错误，没有付款成功！');
            }
        } else {
            $this->assign('sms_shop_money', $sms_shop_money);
            $this->assign('sms_shop_small', $sms_shop_small);
            $this->assign('sms_shop_big', $sms_shop_big);
            $this->assign('nums', $nums);
            $this->display();
        }
    }
	
	//商家等级权限 
	public function grade(){
        $Shopgrade = D('Shopgrade');
        import('ORG.Util.Page');
        $map = array();
        $count = $Shopgrade->where($map)->count();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $list = $Shopgrade->where($map)->order(array('orderby' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		foreach ($list as $k => $val) {
            $list[$k]['shop_count'] = $Shopgrade->get_shop_count($val['grade_id']);
        }
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
	
	//商家等级权限 
	public function permission($grade_id = 0){
        $grade_id = (int) $grade_id;
        $obj = D('Shopgrade');
        if (!($detail = $obj->find($grade_id))) {
            $this->baoError('请选择要查看的商家等级');
        }
        $this->assign('detail', $detail);
        $this->display();
    }
	
	//购买等级权限
	public function pay_permission(){
        $grade_id = (int) $this->_param('grade_id');
		$shop_id = (int) $this->_param('shop_id');
        if (!$obj = D('Shopgradeorder')->shop_pay_grade($grade_id,$shop_id)) {
			$this->baoError(D('Shopgradeorder')->getError(), 3000, true);	
        }else{
			 $this->baoSuccess('恭喜您购买等级成功', U('shop/grade'));
		}
        $this->display();
    }
}