<?php

class TuancodeAction extends CommonAction {
	
	public function _initialize() {
        parent::_initialize();
		if($this->workers['tuan'] != 1){
          $this->error('对不起，您无权限，请联系掌柜开通');
        }
		
    }

    
    public function weixin() {
        $code_id = $this->_get('code_id');
        if (!$detail = D('Tuancode')->find($code_id)) {
            $this->error('没有该套餐码');
        }
        if ($detail['user_id'] != $this->uid) {
            $this->error("套餐码不存在！");
        }
		
		 if($detail['shop_id'] != $this->shop_id){
                $this->error('请不要操作其他店铺的套餐劵');
            }
			
			
        if ($detail['status'] != 0 || $detail['is_used'] != 0) {
            $this->error('该套餐码属于不可消费的状态');
        }

        $url = U('weixin/tuan', array('code_id' => $code_id, 't' => NOW_TIME, 'sign' => md5($code_id . C('AUTH_KEY') . NOW_TIME)));

        $token = 'tuancode_' . $code_id;

        $file = baoQrCode($token, $url);
        $this->assign('file', $file);
        $this->assign('detail', $detail);
        $this->display();
    }
	
	

}
