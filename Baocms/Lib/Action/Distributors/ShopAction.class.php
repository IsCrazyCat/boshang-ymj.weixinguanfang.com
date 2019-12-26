<?php
class ShopAction extends CommonAction
{
    private $photo_create_fields = array('title', 'photo', 'orderby');
    public function about()
    {
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('addr', 'contact', 'tel','mobile', 'business_time', 'delivery_time', 'is_ele_print','is_tuan_print','is_goods_print','is_ding_print','apiKey', 'mKey', 'partner', 'machine_code'));
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->fengmiMsg('店铺地址不能为空');
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
            $data['business_time'] = htmlspecialchars($data['business_time']);
            $data['shop_id'] = $this->shop_id;
            $data['delivery_time'] = (int) $data['delivery_time'];
			
			$data['is_ele_print'] = (int) $_POST['is_ele_print'];
			$data['is_tuan_print'] = (int) $_POST['is_tuan_print'];
			$data['is_goods_print'] = (int) $_POST['is_goods_print'];
			$data['is_ding_print'] = (int) $_POST['is_ding_print'];
			
			
            $data['apiKey'] = htmlspecialchars($data['apiKey']);
            $data['mKey'] = htmlspecialchars($data['mKey']);
            $data['partner'] = htmlspecialchars($data['partner']);
            $data['machine_code'] = htmlspecialchars($data['machine_code']);
            $data['service'] = $data['service'];
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->fengmiMsg('商家介绍含有敏感词：' . $words);
            }
            $ex = array('details' => $details, 'near' => $data['near'], 'business_time' => $data['business_time'], 'delivery_time' => $data['delivery_time']);
            unset($data['business_time'], $data['near'], $data['delivery_time']);
            if (false !== D('Shop')->save($data)) {
                D('Shopdetails')->upDetails($this->shop_id, $ex);
                $this->fengmiMsg('操作成功', U('shop/about'));
            }
            $this->fengmiMsg('操作失败');
        } else {
            $this->assign('ex', D('Shopdetails')->find($this->shop_id));
            $this->display();
        }
    }
    //图片列表
    public function photo()
    {
        $Shoppic = D('Shoppic');
        $map = array('shop_id' => $this->shop_id);
        $list = $Shoppic->where($map)->order(array('orderby' => 'desc'))->select();
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('sig', md5($this->shop_id . C('AUTH_KEY')));
        $this->display();
        // 输出模板
    }
    //传图
    public function photo_create()
    {
        if ($this->isPost()) {
            $data = $this->photo_createCheck();
            $obj = D('Shoppic');
            if ($obj->add($data)) {
                $this->fengmiMsg('添加成功，请等待网站管理员审核', U('shop/photo'));
            }
            $this->fengmiMsg('操作失败！');
        } else {
            $this->display();
        }
    }
    private function photo_createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->photo_create_fields);
        $data['shop_id'] = $this->shop_id;
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->fengmiMsg('标题不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->fengmiMsg('请上传环境图图片');
        }
        if (!isImage($data['photo'])) {
            $this->fengmiMsg('环境图图片格式不正确');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function photo_delete($pic_id = 0)
    {
        $pic_id = (int) $pic_id;
        $obj = D('Shoppic');
        $detail = $obj->find($pic_id);
        if ($detail['shop_id'] == $this->shop_id) {
            $obj->delete($pic_id);
            $this->ajaxReturn(array('status' => 'success', 'msg' => '删除成功', U('shop/photo')));
        }
        $this->ajaxReturn(array('status' => 'error', 'msg' => '访问错误！'));
    }
}