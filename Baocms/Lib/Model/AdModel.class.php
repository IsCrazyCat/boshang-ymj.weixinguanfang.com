<?php
class AdModel extends CommonModel{
    protected $pk   = 'ad_id';
    protected $tableName =  'ad';
	
	public function click_number($ad_id){
		$obj = D('Ad');
		if(false!== $obj->where(array('ad_id'=>$ad_id))->setInc('click',1)){
            return true;
        }else{
           return false;
        }

	}
}