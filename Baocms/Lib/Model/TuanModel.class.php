<?php
class TuanModel extends CommonModel{

    protected $pk = 'tuan_id';
    protected $tableName = 'tuan';
	
	
	public function getError() {
        return $this->error;
    }
	
    public function _format($data){
        $data['save'] = round(($data['price'] - $data['tuan_price']) / 100, 2);
        $data['price'] = round($data['price'] / 100, 2);
        $data['tuan_price'] = round($data['tuan_price'] / 100, 2);
        $data['mobile_fan'] = round($data['mobile_fan'] / 100, 2);
        $data['settlement_price'] = round($data['settlement_price'] / 100, 2);
        $data['discount'] = round($data['tuan_price'] * 10 / $data['price'], 1);
        return $data;
    }
    public function getParentsId($id){
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id'];
        $parent_id2 = $data[$parent_id]['parent_id'];
        if ($parent_id2 == 0) {
            return $parent_id;
        }
        return $parent_id2;
    }
	
	public function check_add_use_integral($use_integral,$settlement_price){
        $config = D('Setting')->fetchAll();
        $integral = $config['integral']['buy'];
		if($integral == 0){
			if ($use_integral % 100 != 0) {
				$this->error = '积分必须为100的倍数';
				return false;
			}
			if ($use_integral > $settlement_price) {
				$this->error = '积分兑换数量必须小于'.$settlement_price.','.'并是100的倍数';
				return false;
			}
		}elseif($integral == 10){
			if ($use_integral % 10 != 0) {
				$this->error = '积分必须为10的倍数';
			}
			if ($use_integral*10 > $settlement_price) {
				$this->error = '积分兑换数量必须小于'.($settlement_price/10).','.'并是10的倍数';
				return false;
			}
		}elseif($integral == 100){
			if ($use_integral % 1 != 0) {
				$this->error = '积分必须为1的倍数';
				return false;
			}
			if ($use_integral*100 > $settlement_price) {
				$this->error = '积分兑换数量必须小于'.($settlement_price/100).','.'并是1的倍数';
				return false;
			}	
		}else{
			$this->error = '后台设置的消费抵扣积分比例不合法';
			return false;
		}
		return true;
    }
	
	
    public function CallDataForMat($items){
        //专门针对CALLDATA 标签处理的
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