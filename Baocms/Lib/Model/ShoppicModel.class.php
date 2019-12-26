<?php
class ShoppicModel extends CommonModel{
    protected $pk = 'pic_id';
    protected $tableName = 'shop_pic';
	public function get_shop_pic_array($shop_id){
		$shop_id = (int) $shop_id;
		
		$get_list_shoppic = D('Shoppic')->Field('photo as get_photo')->order(array('pic_id' => 'desc'))->where(array('shop_id' => $shop_id,'audit'=>1))->select();
		$get_list_shop_dianping_pic = D('Shopdianpingpics')->Field('pic as get_photo')->order(array('pic_id' => 'desc'))->where(array('shop_id' => $shop_id))->select();
		
		$shopdetails = D('Shopdetails')->find($shop_id);
		$get_list_details = getImgs($shopdetails['details']);

        if(!empty($get_list_details)){
			$get_list_shop_details = array();
			foreach ($get_list_details as $key => $value) {
				$get_list_shop_details[]=array('get_photo'=>$value);
			}
		}
	
        $list = array();
		if(!empty($get_list_shoppic)){
			$list = array_merge($list,$get_list_shoppic);
		}
		if(!empty($get_list_shop_details)){
			$list = array_merge($list,$get_list_shop_details);
		}
		if(!empty($get_list_shop_dianping_pic)){
			$list = array_merge($list,$get_list_shop_dianping_pic);
		}
		$list = array_slice($list,$Page->firstRow,$Page->listRows);
		return $list;
	}
}