<?php
class CommunityadModel extends CommonModel{
    protected $pk = 'ad_id';
    protected $tableName = 'community_ad';
	
	public function click_community_number($ad_id){
		if(false!== $this->where(array('ad_id'=>$ad_id))->setInc('click',1)){
            return true;
        }else{
           return false;
        }

	}
}