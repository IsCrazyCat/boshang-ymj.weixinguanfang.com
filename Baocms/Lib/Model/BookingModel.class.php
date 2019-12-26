<?php


class BookingModel extends CommonModel{
    protected $pk   = 'shop_id';
    protected $tableName =  'booking';
    public function items($filter=array(), $orderby=array()){
		$ext_table = $ext_where = '';
        if($type_id = (int)$filter['type_id']){
            $ext_table = "LEFT JOIN ".$this->tablePrefix.'booking_attr'." b ON a.shop_id=b.shop_id";
			$ext_where = "b.type_id=$type_id";
        }
        unset($filter['type_id']);
        if($orderby){
            if(is_string($orderby)){
                $orderby = 'ORDER BY ' . $orderby;
            }else{
                $orderby2 = "";
                $i=1;
                foreach($orderby as $k=>$val){
                    if($i==1){
                        $orderby2 .= $k.' '.$val;
                    }else{
                        $orderby2 .= ','.$k.' '.$val;
                    }
                  $i++;  
                }
                $orderby = 'ORDER BY ' . $orderby2;
            }
        }
        $items = array();
        $sql = "SELECT * FROM ".$this->tablePrefix . $this->tableName." a $ext_table  WHERE $where $ext_where $orderby";
        if($items = $this->query($sql)){
             return $items;
        }
    }
    
    public function getDingType(){
        return array(
            1=>'商务宴请',
            2=>'特色美食',
            3=>'会议婚庆',
        );
    }
    
    public function getType(){
        return  array(
           1 => '1-2人',
           2 => '3-5人',
           3 => '5-8人',
           4 => '8-12人',
           5 => '12人以上',
        );
    }
    
    public function getPrice()
	{
		return  array(
            1 => '50元以下',
            2 => '50-100元',
            3 => '100-200元',
            4 => '200-300元',
            5 => '300元以上',
		);
	}
    
    
    public function get_time($shop_id){
		$cfg = $this->getCfg();
		$detail = D('Booking')->where(array('shop_id'=>$shop_id))->find();
        $now_hour = date('H:i',NOW_TIME);
        if($now_hour>$detail['stime']){
            $detail['stime'] = $now_hour;
        }
		$tem = array();
		foreach($cfg as $k => $v){
			if($v>=$detail['stime'] && $v<= $detail['ltime']){
				$tem[$k] = $v;
			}
		}
		 return $tem;
	}
     
    
    
    public function getCfg(){
        
        return  array(
            1 => '00:30',
            2 => '01:00',
            3 => '01:30',
            4 => '02:00',
            5 => '02:30',
            6 => '03:00',
            7 => '03:30',
            8 => '04:00',
            9 => '04:30',
            10=> '05:00',
            11=> '05:30',
            12=> '06:00',
            13=> '06:30',
            14=> '07:00',
            15=> '07:30',
            16=> '08:00',
            17=> '08:30',
            18=> '09:00',
            19=> '09:30',
            20=> '10:00',
            21=> '10:30',
            22=> '11:00',
            23=> '11:30',
            24=> '12:00',
            25=> '12:30',
            26=> '13:00',
            27=> '13:30',
            28=> '14:00',
            29=> '14:30',
            30=> '15:00',
            31=> '15:30',
            32=> '16:00',
            33=> '16:30',
            34=> '17:00',
            35=> '17:30',
            36=> '18:00',
            37=> '18:30',
            38=> '19:00',
            39=> '19:30',
            40=>'20:00',
            41=>'20:30',
            42=>'21:00',
            43=>'21:30',
            44=>'22:00',
            45=>'22:30',
            46=>'23:00',
            47=>'23:30',
            48=>'24:00',
        );
    }
}