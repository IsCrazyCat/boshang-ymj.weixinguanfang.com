<?php
class NearModel extends CommonModel{
    protected $pk   = 'pois_id';
    protected $tableName =  'pois_content';
	
    public function getType() {
        return array(
			1 => '送水', 
			2 => '快递', 
			3 => '厕所', 
			4 => '小区',
			5 => '装修', 
			6 => '公园',
			7 => '家政', 
			2 => '快递',
			1 => '送水', 
			2 => '快递',
			8 => '医院', 
			9 => '药店',
			10 => '小吃', 
			11 => '宠物',
			12 => '交通', 
			13 => '银行',
			14 => '政府', 
			15 => '通信',
			16 => '工作', 
			17 => '餐饮',
			18 => '超市', 
			19 => '商场',
			20 => '蛋糕', 
			21 => '小吃',
			22 => '服装', 
			23 => '家电',
			24 => '宾馆', 
			25 => '丽人',
			26 => '农贸', 
			27 => '外卖',
			28 => '数码', 
			29 => '休闲',
			30 => '茶楼', 
			31 => 'KTV',
			32 => '酒吧', 
			33 => '洗浴',
			34 => '按摩', 
			35 => '保健',
			36 => '健身', 
			37 => '瑜伽',
			38 => '农贸', 
			39 => '电源',
			40 => '网吧', 
		);
    }
	
	//通过云端获取纠偏后的坐标
    public function GetLocation($latt=0,$lngg=0) {
		if($latt!=0 && $lngg!=0){
			$json = D('Cloud')->GetLocation($latt,$lngg);
			if(!empty($json)){
				$arr =json_decode($json);
				$lat = $arr->Lat;
				$lng = $arr->Lng;
			}
		}else{
			if(isWx() == true){
				if($this->member['lat']!='' && $this->member['lng']!=''){
					$lat = $this->member['lat'];
					$lng = $this->member['lng'];
				}else{
					$lat = cookie('lat');
					$lng = cookie('lng');
				}
			}else{
				$lat = cookie('lat');
				$lng = cookie('lng');
			}
	
		}

		$local=array('lat'=>$lat,'lng'=>$lng);
        return $local;
    }
	
	
	//通过云端获取地址
	public function GetAddress($lat,$lng) {
		if(!empty($lat) && !empty($lng)){
			$json = D('Cloud')->GetAddress($lat,$lng);
			if(!empty($json)){
				$arr =json_decode($json);
				$addr = $arr->formatted_address;
			}
		}
		
		return $addr;
	}
	
}