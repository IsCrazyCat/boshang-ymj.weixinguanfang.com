<?php

class CrowdtypeModel extends CommonModel{
    protected $pk   = 'type_id';
    protected $tableName =  'crowd_type';
    
    
    public function _format($data){
        $data['all_price'] = round($data['price']/100,2); 
        $data['have_price'] = round($data['yunfei']/100,2); 
        return $data;
    }
	//更新费用
	public function crowd_type_need_pay($goods_id,$type_id){
		 $goods_id = (int) $goods_id;
		 $type_id = (int) $type_id;
		 $type = D('Crowdtype')->where(array('goods_id'=>$goods_id,'type_id'=>$type_id))->find();
		 if(!empty($type)){
			 $Crowdtype['price'] = $type['price']; 
			 $Crowdtype['yunfei'] = $type['yunfei']; 
			 $Crowdtype['need_pay'] = $type['price'] + $type['yunfei']; 
			 return $Crowdtype;
		}else{
			return false;	 
		}
    }
}