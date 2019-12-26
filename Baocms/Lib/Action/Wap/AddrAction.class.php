<?php
class AddrAction extends CommonAction
{
    public function update_addr()
    {
        $type = (int) $this->_param('type');
        $this->assign('type', $type);
        $order_id = (int) $this->_param('order_id');
        $this->assign('order_id', $order_id);
        if (!$this->uid) {
            if (empty($this->uid)) {
                header("Location:" . U('passport/login'));
                die;
            }
        }
        $addr_id = I('addr_id', '', 'trim,intval');
        if (!$addr_id) {
            $this->fengmiMsg('错误！');
        } else {
            $ud = D('UserAddr');
            $up1 = $ud->where('user_id =' . $this->uid)->setField('is_default', 0);
            $up2 = $ud->where('addr_id =' . $addr_id)->setField('is_default', 1);
            if ($type == 1) {
                $this->fengmiMsg('恭喜您，操作成功！', U('Wap/ele/pay', array('order_id' => $order_id)));
            } elseif ($type == 2) {
                $this->fengmiMsg('恭喜您，操作成功！', U('Wap/mall/pay', array('order_id' => $order_id)));
            } elseif ($type == 3) {
                $this->fengmiMsg('恭喜您，操作成功！', U('Wap/mart/pay', array('order_id' => $order_id)));
            } else {
                $this->fengmiMsg('恭喜您，操作成功！', U('mcenter/addrs/index'));
            }
        }
    }
    public function add_addr()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '您还没有登录或登录超时！'));
        } else {
            $name = I('name', '', 'trim,htmlspecialchars');
            $area_id = I('area_id', '', 'intval,trim');
            $city_id = I('city_id', '', 'intval,trim');
            $business_id = I('business_id', '', 'intval,trim');
            $mobile = I('mobile', '', 'trim');
            $addr = I('addr', '', 'trim,htmlspecialchars');
            if (!$name) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '联系人没有填写！'));
            }
            /*if (!$city_id || !$area_id || !$business_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '城市、地区、商圈必须都选择！'));
            }*/
            if (!isMobile($mobile)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码不正确！'));
            }
            if (!$addr) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '收货地址没有填写！'));
            }
            $data = array();
            $data['name'] = $name;
            $data['city_id'] = $city_id;
            $data['area_id'] = $area_id;
            $data['business_id'] = $business_id;
            $data['mobile'] = $mobile;
            $data['addr'] = $addr;
            $data['user_id'] = $this->uid;
            $data['is_default'] = 0;
            $data['closed'] = 0;
            $ud = D('UserAddr');
            $add = $ud->add($data);
            if ($add) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '添加成功！'));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '添加失败！'));
            }
        }
    }
    public function edit_addr()
    {
        if (empty($this->uid)) {
            $this->ajaxReturn(array('status' => 'error', 'msg' => '您还没有登录或登录超时！'));
        } else {
            $addr_id = I('addr_id', '', 'trim,intval');
            $name = I('name', '', 'trim,htmlspecialchars');
            $area_id = I('area_id', '', 'intval,trim');
            $city_id = I('city_id', '', 'intval,trim');
            $business_id = I('business_id', '', 'intval,trim');
            $mobile = I('mobile', '', 'trim');
            $addr = I('addr', '', 'trim,htmlspecialchars');
            $ud = D('UserAddr');
            if (!$addr_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '错误！'));
            } else {
                $f = $ud->where('addr_id =' . $addr_id)->find();
                if (!$f) {
                    $this->ajaxReturn(array('status' => 'error', 'msg' => '错误！'));
                } else {
                    if ($f['user_id'] != $this->uid) {
                        $this->ajaxReturn(array('status' => 'error', 'msg' => '非法操作！'));
                    }
                }
            }
            if (!$name) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '联系人没有填写！'));
            }
           /* if (!$city_id || !$area_id || !$business_id) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '城市、地区、商圈必须都选择！'));
            }*/
            if (!isMobile($mobile)) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '手机号码不正确！'));
            }
            if (!$addr) {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '收货地址没有填写！'));
            }
            $data = array();
            $data['name'] = $name;
            $data['city_id'] = $city_id;
            $data['area_id'] = $area_id;
            $data['business_id'] = $business_id;
            $data['mobile'] = $mobile;
            $data['addr'] = $addr;
            $data['user_id'] = $this->uid;
            $data['is_default'] = $f['is_default'];
            $data['closed'] = 0;
            $add = $ud->where('addr_id =' . $addr_id)->setField($data);
            if ($add) {
                $this->ajaxReturn(array('status' => 'success', 'msg' => '修改成功！'));
            } else {
                $this->ajaxReturn(array('status' => 'error', 'msg' => '修改失败！'));
            }
        }
    }
}