<?php
class BookingsettingModel extends CommonModel{
    protected $pk   = 'shop_id';
    protected $tableName =  'booking_setting';
	public function getprice(){
		return  array(
            1 => '50元以下',
            2 => '50-100元',
            3 => '100-200元',
            4 => '200-300元',
            5 => '300元以上',
		);
	}
	//获取包厢整体关闭状态
	public function get_booking_setting($shop_id){
		$detail = D('Bookingsetting')->where(array('shop_id'=>$shop_id))->find();
		return $detail;
	}
	
	
	public function pricesql($key){
		$p = $this->getprice();
		if($d = $p[$key]){
			if(strpos($d,'-') !== false){
				preg_match("/(\d+)\-(\d+)/i",$d,$m);
			}else{
				preg_match("/(\d+)/i",$d,$m);
				if(strpos($d,'以上') !== false){
					$m['3'] = '>=';
				}else{
					$m['3'] = '<=';
				}
			}
			return $m;
		}
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


    public function detail($shop_id){
        $shop_id = (int)$shop_id;
        $data = $this->find($shop_id);
        if(empty($data)){
            $data = array('shop_id'=>$shop_id,'mobile'=>'','money'=>2000,'bao_time'=>3,'start_time'=>0,'end_time'=>0,'is_bao'=>0,'id_ting'=>0);
            $this->add($data);
        }
        return $data;
    }

	public function get_time($shop_id){
		$cfg = $this->getCfg();
		$detail = D('Bookingsetting')->where(array('shop_id'=>$shop_id))->find();
		
		$tem = array();
		foreach($cfg as $k => $v){
			if($k>=$detail['start_time'] && $k<= $detail['end_time']){
				$tem[$k] = $v;
			}
		}
		 return $tem;
	}

	public function get_is_room($shop_id,$data)
	{
		$get_time = $this->get_time($shop_id);
		$setting = $this->where('shop_id='.$shop_id)->find();
		$arr = array('shop_id'=>$shop_id,'last_date'=>$data['date'],'room_id'=>$data['roomid'],'is_pay'=>'1');
		$yuyue = D('Bookingyuyue')->where($arr)->select();
		$tem = $t = array();
		$no_time = $setting['bao_time']*2;
		foreach($yuyue as $k => $v){
			foreach($get_time as $kk => $vv){
				if($kk>$v['last_t']-$no_time && $kk<$v['last_t']+$no_time){
					unset($get_time[$kk]);
				}
			}
		}
		return $get_time;
	}

	public function get_time_k($time)
	{
		$Cfg = $this->getCfg();
		foreach($Cfg as $k => $v){
			if($v == $time){
				return $k;
			}
		}
	}
    
}