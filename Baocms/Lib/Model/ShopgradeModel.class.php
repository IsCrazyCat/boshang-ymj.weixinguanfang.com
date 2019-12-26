<?php
class ShopgradeModel extends CommonModel {
    protected $pk = 'grade_id';
    protected $tableName = 'shop_grade';
	//统计当前等级下面多少商家
	public function get_shop_count($grade_id){
        $shop_count = D('Shop')->where(array('grade_id'=>$grade_id))->count();
        if (!empty($shop_count)) {
			return $shop_count;
        }else{
			 return false;
		}
    }

}
