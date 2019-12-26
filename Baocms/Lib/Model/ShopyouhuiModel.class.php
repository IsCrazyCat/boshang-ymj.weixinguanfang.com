<?php
class ShopyouhuiModel extends CommonModel {

    protected $pk = 'yh_id';
    protected $tableName = 'shop_youhui';

    public function get_amount($shop_id,$amount,$exception){
        $youhui = $this->where(array('shop_id'=>$shop_id,'is_open'=>1))->find();
        $need = $amount - $exception;
        if($youhui['type_id'] == 0){
            $result = round($need *$youhui['discount']/10,2) + $exception; 
        }else{
            $t = (int)$need/$youhui['min_amount'];
            $result = $need - $t*$youhui['amount'] + $exception;
        }
        return $result;
    }
   public function get_file_Code($shop_id,$size){
        $url = U('wap/shop/breaks', array('shop_id' => $shop_id, 't' => NOW_TIME, 'sign' => md5($shop_id . C('AUTH_KEY') . NOW_TIME)));
        $token = 'shop_id_' . $shop_id;
        $file = fengmiQrCode($token, $url,$size);
        return $file;
    } 
    
}
