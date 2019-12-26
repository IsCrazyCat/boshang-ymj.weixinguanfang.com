<?php
class EleModel extends CommonModel{
    protected $pk = 'shop_id';
    protected $tableName = 'ele';
    public function updateMonth($shop_id){
        $shop_id = (int) $shop_id;
        $month = date('Ym', NOW_TIME);
        $num = (int) D('Eleorder')->where(array('shop_id' => $shop_id, 'month' => $month))->count();
        return $this->execute("update " . $this->getTableName() . " set  month_num={$num} where shop_id={$shop_id}");
    }
    public function getEleCate(){
        return array('1' => '快餐简餐', '2' => '正餐', '3' => '馋嘴小吃', '4' => '甜点饮料', '5' => '生活超市', '6' => '水果蔬菜');
    }
	public function get_file_Code($shop_id,$size){
        $url = U('wap/shop/ele', array('shop_id' => $shop_id, 't' => NOW_TIME, 'sign' => md5($shop_id . C('AUTH_KEY') . NOW_TIME)));
        $token = 'shop_id_' . $shop_id;
        $file = fengmiQrCode($token, $url,$size);
        return $file;
    }
    public function CallDataForMat($items){
        if (empty($items)) {
            return array();
        }
        $obj = D('Shop');
        $shop_ids = array();
        foreach ($items as $k => $val) {
            $shop_ids[$val['shop_id']] = $val['shop_id'];
        }
        $shops = $obj->itemsByIds($shop_ids);
        foreach ($items as $k => $val) {
            $val['shop'] = $shops[$val['shop_id']];
            $items[$k] = $val;
        }
        return $items;
    }
}