<?php



class ShopdetailsModel extends CommonModel{
    protected $pk   = 'shop_id';
    protected $tableName =  'shop_details';
     public function upDetails($shop_id,$data){
        $shop_id = (int)$shop_id;
        $data['shop_id'] = $shop_id;
        $rows = $this->find($shop_id);
        if($rows){
            $this->save($data);
        }else{
            $this->add($data);
        }
        return true;
    }
    
    

    
}