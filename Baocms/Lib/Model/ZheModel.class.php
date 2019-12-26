<?php
class 	ZheModel extends CommonModel{
    protected $pk = 'zhe_id';
    protected $tableName = 'zhe';
    
    public function getZheWeek(){
        return array(
			'1' => '星期一', 
			'2' => '星期二', 
			'3' => '星期三', 
			'4' => '星期四', 
			'5' => '星期五', 
			'6' => '星期六',
			'7' => '星期日'
		);
    }
	
	public function getZheDate(){
        return array(
			'1' => '1日', 
			'2' => '2日', 
			'3' => '3日', 
			'4' => '4日', 
			'5' => '5日', 
			'6' => '6日',
			'7' => '7日',
			'8' => '8日', 
			'9' => '9日', 
			'10' => '10日', 
			'11' => '11日', 
			'12' => '12日', 
			'13' => '13日',
			'14' => '14日',
			'15' => '15日', 
			'16' => '16日', 
			'17' => '17日', 
			'18' => '18日', 
			'19' => '19日', 
			'20' => '20日',
			'21' => '21日',
			'22' => '22日', 
			'23' => '23日', 
			'24' => '24日', 
			'25' => '25日', 
			'26' => '26日', 
			'27' => '27日',
			'28' => '28日',
			'29' => '29日', 
			'30' => '30日', 
			'31' => '31日', 
		);
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