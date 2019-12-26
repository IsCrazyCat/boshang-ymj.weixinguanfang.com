<?php
class BookingroomModel extends CommonModel
{
    protected $pk = 'room_id';
    protected $tableName = 'booking_room';
    public function getType()
    {
        return array(1 => '1-2人', 2 => '3-5人', 3 => '5-8人', 4 => '8-12人', 5 => '12人以上');
    }
    //public function
    public function shoptype($shop_id)
    {
        $room = $this->where('shop_id = ' . $shop_id)->order(array('type_id' => 'asc'))->select();
        $type = $this->getType();
        $arr = array();
        foreach ($room as $k => $v) {
            $arr[$v['type_id']] = $type[$v['type_id']];
        }
        return $arr;
    }
	//获取包厢商家图片组合
	public function get_room_thumb($room_id){
		$detail = D('Bookingroom')->where(array('room_id'=>$room_id))->find();
		if($detail['photo']){
           $get_detail_photo[]=array('pic_id'=>$detail['photo']);
		}
        $thumb = unserialize($detail['thumb']);
		if(!empty($thumb)){
             $get_thumb = array();
             foreach ($thumb as $key => $value) {
               $get_thumb[]=array('pic_id'=>$value);
             }
         }
		 
		 $photo_list = array();
            if(!empty($get_detail_photo)){
               $photo_list = array_merge($photo_list,$get_detail_photo);
            }
			if(!empty($get_thumb)){
               $photo_list = array_merge($photo_list,$get_thumb);
            }
			
			$list = array_slice($photo_list,$Page->firstRow,$Page->listRows);	
			if (!empty($list)) {
				return $list;
			}

    }
	
	
    public function getroom($shop_id, $date, $time, $reson){
        $setting = D('Bookingsetting')->where('shop_id=' . $shop_id)->find();
		
        $type = $this->getType();
        $room = $this->where('shop_id = ' . $shop_id)->order(array('type_id' => 'asc'))->select();
        $arr = array();
        foreach ($room as $k => $v) {
            $arr[$v['type_id']][] = $v;
        }
        $no_time = $setting['bao_time'] * 2;
        $Cfg = D('Bookingsetting')->getCfg();
        foreach ($Cfg as $k => $v) {
            if ($v == $time) {
                $t = $k;
            }
        }
        $s = $t - $no_time;
        $e = $t + $no_time;
        $tem = array();
        $is_yuyue = D('Bookingyuyue')->where('last_date ="' . $date . '" and is_pay=1 and shop_id =' . $shop_id . ' and last_t>=' . $s . ' and last_t<=' . $e)->select();
        if ($is_yuyue) {
            foreach ($is_yuyue as $k => $v) {
                $tem[$v['room_id']] = $v['room_id'];
            }
        }
        foreach ($arr as $k => $v) {
            foreach ($tem as $kk => $vv) {
                foreach ($v as $kkk => $vvv) {
                    if ($vvv['room_id'] == $vv) {
                        $arr[$k][$kkk]['is_yuyue'] = 1;
                    }
                }
            }
        }
        return $arr;
    }
	//获取单个包厢的名称
	 public function get_shop_room($shop_id,$room_id){
        $room = $this->where(array('shop_id'=>$shop_id,'room_id'=>$room_id))->find();
        return $room;
    }
	
	
    public function get_room_d($shop_id){
        $room = $this->where('shop_id = ' . $shop_id)->order(array('type_id' => 'asc'))->select();
        $tem = array();
        foreach ($room as $k => $v) {
            $tem[$v['room_id']] = $v;
        }
        return $tem;
    }
    //MOBILE
    public function getrooms($shop_id, $date, $time, $reson){
		
        $type = $this->getType();
		$type_id = array_search($reson, $type);
        $room = $this->where(array('shop_id'=>$shop_id,'type_id'=>$type_id))->order(array('type_id' => 'asc'))->select();
        return $room;
    }
}