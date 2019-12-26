<?php
class GoodsAction extends CommonAction{

    private $create_fields = array('title', 'photo', 'cate_id', 'intro', 'guige', 'num','is_reight','weight', 'kuaidi_id','select1', 'select2', 'select3', 'select4', 'select5', 'price', 'shopcate_id', 'mall_price', 'use_integral', 'instructions', 'details', 'end_date', 'is_vs1', 'is_vs2', 'is_vs3', 'is_vs4', 'is_vs5', 'is_vs6','profit_enable','profit_rate1','profit_rate2','profit_rate3','profit_rank_id');
    private $edit_fields = array('title', 'photo', 'cate_id', 'intro', 'guige', 'num', 'is_reight','weight','kuaidi_id','select1', 'select2', 'select3', 'select4', 'select5', 'price', 'shopcate_id', 'mall_price', 'use_integral','end_date', 'is_vs1', 'is_vs2', 'is_vs3', 'is_vs4', 'is_vs5', 'is_vs6','profit_enable','profit_rate1','profit_rate2','profit_rate3','profit_rank_id', 'audit');
    public function _initialize(){
        parent::_initialize();
        $this->autocates = D('Goodsshopcate')->where(array('shop_id' => $this->shop_id))->select();
        $this->assign('autocates', $this->autocates);
		$this->GoodsCates = D('Goodscate')->fetchAll();
        $this->assign('GoodsCates', $this->GoodsCates);
    }

    private function check_weidian(){
        $wd = D('WeidianDetails');
        $wd_res = $wd->where('shop_id =' . $this->shop_id)->find();
        if (!$wd_res) {
            $this->error('请先完善微店资料！', U('goods/weidian'));
        } elseif ($wd_res['audit'] == 0) {
            $this->error('您的微店正在审核中，请耐心等待！', U('index/index'));
        } elseif ($wd_res['audit'] == 2) {
            $this->error('您的微店未通过审核！', U('index/index'));
        }
    }

    public function weidian(){
        $gc = D('GoodsCate');
        $select = $gc->where('parent_id =0')->select();
        $this->assign('select', $select);
        $wd = D('WeidianDetails');
        $weidian = $wd->where('shop_id =' . $this->shop_id)->find();
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('weidian_name', 'addr', 'city_id', 'area_id', 'cate_id', 'business_time', 'details', 'pic', 'lng', 'lat', 'reg_time'));
            if (empty($weidian)) {
                $data['weidian_name'] = htmlspecialchars($data['weidian_name']);
                if (empty($data['weidian_name'])) {
                    $this->fengmiMsg('店铺名称不能为空');
                }
                $data['addr'] = htmlspecialchars($data['addr']);
                if (empty($data['addr'])) {
                    $this->fengmiMsg('店铺地址不能为空');
                }
                $data['cate_id'] = (int) $data['cate_id'];
                if (empty($data['cate_id'])) {
                    $this->fengmiMsg('店铺分类没有选择');
                }
                $data['city_id'] = intval($data['city_id']);
                $data['area_id'] = intval($data['area_id']);
                if (empty($data['city_id']) || empty($data['area_id'])) {
                    $this->fengmiMsg('城市或地区没有选择');
                }
                $data['reg_time'] = NOW_TIME;
            } else {
                $data['update_time'] = NOW_TIME;
            }
            $data['business_time'] = htmlspecialchars($data['business_time']);
            $data['shop_id'] = $this->shop_id;
            if (empty($data['pic'])) {
                $this->fengmiMsg('店铺图标没有上传');
            }
            if (empty($data['lng']) || empty($data['lat'])) {
                $this->fengmiMsg('店铺坐标没有选择');
            }
            $data['details'] = $this->_post('details', 'SecurityEditorHtml');
            if (empty($data['details']) || $data['details'] == null) {
                $this->fengmiMsg('详情没有填写');
            }
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->fengmiMsg('商家介绍含有敏感词：' . $words);
            }
            if (!$weidian) {
                $add = $wd->add($data);
                if (!$add) {
                    $this->fengmiMsg('设置失败');
                } else {
                    $this->fengmiMsg('设置成功', U('goods/weidian'));
                }
            } else {
                $up = $wd->where('shop_id =' . $this->shop_id)->save($data);
                if (!$up) {
                    $this->fengmiMsg('修改失败');
                } else {
                    $this->fengmiMsg('修改成功', U('goods/weidian'));
                }
            }
        } else {
            $this->assign('the_shop', D('Shop')->where('shop_id =' . $this->shop_id)->find());
            $cates = D('Weidiancate')->fetchAll();
            $this->assign('cates', $cates);
            $this->assign('weidian', $weidian);
            $lat = addslashes(cookie('lat'));
            $lng = addslashes(cookie('lng'));
            if (empty($lat) || empty($lng)) {
                $lat = $this->_CONFIG['site']['lat'];
                $lng = $this->_CONFIG['site']['lng'];
            }
            if ($business_id = (int) $this->_param('business_id')) {
                $map['business_id'] = $business_id;
                $this->assign('business_id', $business_id);
            }
            $this->assign('citys', D('City')->fetchAll());
            $this->assign('areas', D('Area')->fetchAll());
            $this->assign('business', D('Business')->fetchAll());
            $this->assign('lat', $lat);
            $this->assign('lng', $lng);
            $areas = D('Area')->fetchAll();
            $this->display();
        }
    }
    public function weidian_child($parent_id = 0){
        $datas = D('Weidiancate')->fetchAll();
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
    public function get_select(){
        if (IS_AJAX) {
            $pid = I('pid', 0, 'intval,trim');
            $gc = D('GoodsCate');
            $list = $gc->where('parent_id =' . $pid)->select();
            if ($pid == 0) {
                $this->ajaxReturn(array('status' => 'success', 'list' => ''));
            }
            if ($list) {
                $l = '';
                foreach ($list as $k => $v) {
                    $l = $l . '<option value=' . $v['cate_id'] . ' style="color:#333333;">' . $v['cate_name'] . '</option>';
                }
                $this->ajaxReturn(array('status' => 'success', 'list' => $l));
            }
        }
    }
    public function create(){
        $this->check_weidian();
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Goods');
            if ($goods_id = $obj->add($data)) {
                $wei_pic = D('Weixin')->getCode($goods_id, 3);
                $obj->save(array('goods_id' => $goods_id, 'wei_pic' => $wei_pic));
				//图片
				$photos = $this->_post('photos', false);
                if (!empty($photos)) {
                    D('Goodsphoto')->upload($goods_id, $photos);
                }
                $this->fengmiMsg('添加成功,请等待审核', U('mart/index'));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->assign('cates', D('Goodscate')->fetchAll());
			$this->assign('kuaidi', D('Pkuaidi')->where(array('shop_id'=>$this->shop_id,'type'=>goods))->select());
            $this->display();
        }
    }
    private function createCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg('产品名称不能为空');
        }
   
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->fengmiMsg('副标题不能为空');
        }
        $data['guige'] = htmlspecialchars($data['guige']);
        if (empty($data['guige'])) {
            $this->fengmiMsg('规格不能为空');
        }
		$data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('库存不能为空');
        } 
		$data['is_reight'] = (int) $data['is_reight'];
		$data['weight'] = (int) $data['weight'];
		if ($data['is_reight'] == 1) {
			if (empty($data['weight'])) {
             	$this->baoError('重量不能为空');
			}
			if ($data['weight'] % 1 != 0) {
				$this->baoError('重量必须为1的倍数');
			}
        }
		$data['kuaidi_id'] = (int) $data['kuaidi_id'];
		if ($data['is_reight'] == 1) {
			if (empty($data['kuaidi_id'])) {
				$this->baoError('运费模板不能为空');
			}
		}	
        $data['shop_id'] = $this->shop_id;
        $shopdetail = D('Shop')->find($this->shop_id);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('请选择分类');
        }
        $data['shopcate_id'] = (int) $data['shopcate_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->fengmiMsg('市场价格不能为空');
        }
        $data['mall_price'] = (int) ($data['mall_price'] * 100);
        if (empty($data['mall_price'])) {
            $this->fengmiMsg('商城价格不能为空');
        }
		$data['use_integral'] = (int) $data['use_integral'];
		//商城检测积分合法性开始
		if (!D('Goods')->check_add_use_integral($data['use_integral'],$data['mall_price'])) {//传2参数
            $this->fengmiMsg(D('Goods')->getError());
        }
		//商城检测积分合法性结束
        $data['instructions'] = SecurityEditorHtml($data['instructions']);
        if (empty($data['instructions'])) {
            $this->fengmiMsg('购买须知不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['instructions'])) {
            $this->fengmiMsg('购买须知含有敏感词：' . $words);
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->fengmiMsg('商品详情不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->fengmiMsg('商品详情含有敏感词：' . $words);
        }
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->fengmiMsg('过期时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->fengmiMsg('过期时间格式不正确');
        }
        $data['is_vs1'] = (int) $data['is_vs1'];
        $data['is_vs2'] = (int) $data['is_vs2'];
        $data['is_vs3'] = (int) $data['is_vs3'];
        $data['is_vs4'] = (int) $data['is_vs4'];
        $data['is_vs5'] = (int) $data['is_vs5'];
        $data['is_vs6'] = (int) $data['is_vs6'];
        $data['select1'] = (int) $data['select1'];
        $data['select2'] = (int) $data['select2'];
        $data['select3'] = (int) $data['select3'];
        $data['select4'] = (int) $data['select4'];
        $data['select5'] = (int) $data['select5'];
		$data['profit_enable'] = (int) $data['profit_enable'];
        $data['profit_rate1'] = (int) $data['profit_rate1'];
        $data['profit_rate2'] = (int) $data['profit_rate2'];
        $data['profit_rate3'] = (int) $data['profit_rate3'];
		
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['sold_num'] = 0;
        $data['view'] = 0;
        $data['is_mall'] = 1;
        return $data;
    }
    public function edit($goods_id = 0){
        $this->check_weidian();
        if ($goods_id = (int) $goods_id) {
            $obj = D('Goods');
            if (!($detail = $obj->find($goods_id))) {
                $this->error('请选择要编辑的商品');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要试图越权操作其他人的内容');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['goods_id'] = $goods_id;
                if (!empty($detail['wei_pic'])) {
                    if (true !== strpos($detail['wei_pic'], "https://mp.weixin.qq.com/")) {
                        $wei_pic = D('Weixin')->getCode($goods_id, 3);
                        $data['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($goods_id, 3);
                    $data['wei_pic'] = $wei_pic;
                }
                if (false !== $obj->save($data)) {
					//图片
					$photos = $this->_post('photos', false);
					if (!empty($photos)) {
						D('Goodsphoto')->upload($goods_id, $photos);
					}
                    $this->fengmiMsg('编辑成功，请联系管理员审核', U('mart/index'));
                }
                $this->fengmiMsg('操作失败');
            } else {
                $this->assign('detail', $obj->_format($detail));
                $this->assign('parent_id', D('Goodscate')->getParentsId($detail['cate_id']));
                $this->assign('attrs', D('Goodscateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $detail['cate_id']))->select());
                $this->assign('cates', D('Goodscate')->fetchAll());
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->assign('photos', D('Goodsphoto')->getPics($goods_id));
				$this->assign('kuaidi', D('Pkuaidi')->where(array('shop_id'=>$this->shop_id,'type'=>goods))->select());
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的商品');
        }
    }
    private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg('产品名称不能为空');
        }
        $data['shop_id'] = (int) $this->shop_id;
        if (empty($data['shop_id'])) {
            $this->fengmiMsg('商家不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->fengmiMsg('副标题不能为空');
        }
 
        $data['guige'] = htmlspecialchars($data['guige']);
        if (empty($data['guige'])) {
            $this->fengmiMsg('规格不能为空');
        }
		$data['num'] = (int) $data['num'];
        if (empty($data['num'])) {
            $this->baoError('库存不能为空');
        } 
		$data['is_reight'] = (int) $data['is_reight'];
		$data['weight'] = (int) $data['weight'];
		if ($data['is_reight'] == 1) {
			if (empty($data['weight'])) {
             	$this->baoError('重量不能为空');
			}
			if ($data['weight'] % 1 != 0) {
				$this->baoError('重量必须为1的倍数');
			}
        }
		$data['kuaidi_id'] = (int) $data['kuaidi_id'];
		if ($data['is_reight'] == 1) {
			if (empty($data['kuaidi_id'])) {
				$this->baoError('运费模板不能为空');
			}
		}	
        $shopdetail = D('Shop')->find($this->shop_id);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->fengmiMsg('请选择分类');
        }

        $data['shopcate_id'] = (int) $data['shopcate_id'];
        $data['area_id'] = $this->shop['area_id'];
        $data['business_id'] = $this->shop['business_id'];
        $data['city_id'] = $this->shop['city_id'];
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('缩略图格式不正确');
        }
        $data['price'] = (int) ($data['price'] * 100);
        if (empty($data['price'])) {
            $this->fengmiMsg('市场价格不能为空');
        }
        $data['mall_price'] = (int) ($data['mall_price'] * 100);
        if (empty($data['mall_price'])) {
            $this->fengmiMsg('商城价格不能为空');
        }
		$data['use_integral'] = (int) $data['use_integral'];
		//商城检测积分合法性开始
		if (!D('Goods')->check_add_use_integral($data['use_integral'],$data['mall_price'])) {//传2参数
            $this->fengmiMsg(D('Goods')->getError());
        }
		//商城检测积分合法性结束
        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->fengmiMsg('过期时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->fengmiMsg('过期时间格式不正确');
        }
        $data['is_vs1'] = (int) $data['is_vs1'];
        $data['is_vs2'] = (int) $data['is_vs2'];
        $data['is_vs3'] = (int) $data['is_vs3'];
        $data['is_vs4'] = (int) $data['is_vs4'];
        $data['is_vs5'] = (int) $data['is_vs5'];
        $data['is_vs6'] = (int) $data['is_vs6'];
        $data['select1'] = (int) $data['select1'];
        $data['select2'] = (int) $data['select2'];
        $data['select3'] = (int) $data['select3'];
        $data['select4'] = (int) $data['select4'];
        $data['select5'] = (int) $data['select5'];
		$data['profit_enable'] = (int) $data['profit_enable'];
        $data['profit_rate1'] = (int) $data['profit_rate1'];
        $data['profit_rate2'] = (int) $data['profit_rate2'];
        $data['profit_rate3'] = (int) $data['profit_rate3'];		
        $data['orderby'] = (int) $data['orderby'];
        $data['audit'] = 0;
        return $data;
    }
    public function child($parent_id = 0){
        $datas = D('Goodscate')->fetchAll();
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
    public function ajax($cate_id, $goods_id = 0){
        if (!($cate_id = (int) $cate_id)) {
            $this->error('请选择正确的分类');
        }
        if (!($detail = D('Goodscate')->find($cate_id))) {
            $this->error('请选择正确的分类');
        }
        $this->assign('cate', $detail);
        $this->assign('attrs', D('Goodscateattr')->order(array('orderby' => 'asc'))->where(array('cate_id' => $cate_id))->select());
        if ($goods_id) {
            $this->assign('detail', D('Goods')->find($goods_id));
            $this->assign('maps', D('GoodsCateattr')->getAttrs($goods_id));
        }
        $this->display();
    }
}