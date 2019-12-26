<?php

class  GoodsdetailsModel extends  CommonModel{
    protected $pk   = 'goods_id';
    protected $tableName =  'goods_details';
    
    public function updateDetails($goods_id,$details){
        $data = $this->find($goods_id);
        if($data){
            $this->save(array('goods_id'=>$goods_id,'details'=>$details));
        }else{
            $this->add(array('goods_id'=>$goods_id,'details'=>$details)); 
        }
        return true;
    }
    
}