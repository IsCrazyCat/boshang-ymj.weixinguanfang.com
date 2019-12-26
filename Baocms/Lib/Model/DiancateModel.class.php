<?php

class DiancateModel  extends CommonModel{
    protected $pk   = 'cate_id';
    protected $tableName =  'dian_cate';
    
    
   public function fetchAll($shop_id){
       $shop_id=(int)$shop_id;
       $datas=$this->where(array('shop_id'=>$shop_id))->select();
       $return=array();
       foreach($datas as $key =>$val){
           $return[$val[$this->pk]]=$val;
       }
       return $return;
   }   
       
}
