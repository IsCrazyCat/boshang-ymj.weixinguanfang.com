<?php
class ShopyouhuiAction extends CommonAction {
    private $create_fields = array('yh_id','shop_id', 'type_id', 'discount', 'min_amount', 'amount', 'is_open','audit');
    public function index() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Shopyouhui');
            if(!$data['yh_id']){
                if ($obj->add($data)) {
                    $this->baoSuccess('添加成功', U('shopyouhui/index'));
                    if($this->shop['is_breaks'] == 0){
                        D('Shop')->save(array('shop_id'=>$this->shop_id,'is_breaks'=>1));
                    }
                }
                $this->baoError('操作失败！');
            }else{
                if (false !== $obj->save($data)) {
                   $this->baoSuccess('修改成功', U('shopyouhui/index'));
                }
                $this->baoError('操作失败！');
            }
        }else{
            $this->assign('youhui',D('Shopyouhui')->where(array('shop_id'=>$this->shop_id))->find());
			$file = D('Shopyouhui')->get_file_Code($this->shop_id,100);//生成二维码
            $this->assign('file', $file);
            $this->display(); // 输出模板
        }
        
    }


    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['yh_id'] = (int)$data['yh_id'];
        $data['is_open'] = 1;
        $data['shop_id'] = $this->shop_id;
        if($data['type_id'] == 0){ //折扣优惠
            $data['discount'] = $data['discount'];
            if (empty($data['discount'])) {
                $this->baoError('折扣不能为空');
            }elseif(!is_numeric($data['discount'])){
                $this->baoError('折扣格式不正确');
            }
        }else{
            $data['min_amount'] = $data['min_amount'];
            if (empty($data['min_amount'])) {
                $this->baoError('满多少不能为空');
            }elseif(!is_numeric($data['discount'])){
                $this->baoError('满多少格式不正确');
            }
            $data['amount'] = $data['amount'];
            if (empty($data['amount'])) {
                $this->baoError('减多少不能为空');
            }elseif(!is_numeric($data['discount'])){
                $this->baoError('减多少格式不正确');
            }
        }
		$data['audit'] = 0;
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
		
        return $data;
    }

    public function close(){
        if(false !== D('Shopyouhui')->data(array('is_open'=>0))->where(array('shop_id'=>$this->shop_id))->save()){
            $this->baoSuccess('成功关闭优惠买单',U('shopyouhui/index'));
        }
    }
    
    public function open(){
        if(false !== D('Shopyouhui')->data(array('is_open'=>1))->where(array('shop_id'=>$this->shop_id))->save()){
            $this->baoSuccess('成功开启优惠买单',U('shopyouhui/index'));
        }
    }
    
   
}
