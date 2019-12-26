<?php



class BookingcateModel extends CommonModel{
    protected $pk   = 'cate_id';
    protected $tableName =  'booking_cate';
	
	
	public function get_menu($shop_id){
		$shop_id = (int) $shop_id;
		$cate = D('Bookingcate');
		$cate_list = $cate->where('shop_id = '.$shop_id)->order(array('orderby'=>'asc'))->select();
		$menu = $this->where('closed=0 and shop_id='.$shop_id)->select();

		$tem= array();
		foreach($cate_list as $k => $v){
			foreach($menu as $kk => $vv){
				if($vv['cate_id'] == $v['cate_id']){
					$tem[$v['cate_id']][] = $vv;
				}
			}
		}
		return $tem;
		
	}



	public function get_cate($shop_id){
		$shop_id = (int) $shop_id;
		$menu = $this->get_menu($shop_id);
		$tem= array();
		foreach($menu as $k => $v){
			$tem[$k] = $k;
		}

		$cate = D('Bookingcate');
		$cate_list = $cate->order(array('orderby'=>'asc'))->itemsByIds($tem);
		return $menucates;

	}

}