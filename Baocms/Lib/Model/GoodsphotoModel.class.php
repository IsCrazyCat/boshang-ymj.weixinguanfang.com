<?php


class  GoodsphotoModel extends CommonModel{
    protected $pk   = 'pic_id';
    protected $tableName =  'goods_photos';
    
    public  function upload($goods_id,$photos){
        $this->delete(array("where"=>array('goods_id'=>$goods_id)));
        foreach($photos as $val){
            $this->add(array(
                'goods_id' => $goods_id,
                'photo' => htmlspecialchars($val)
            ));
        }
        return true;
    }
    
    
    public function getPics($goods_id){
        $goods_id = (int) $goods_id;
        return $this->where(array('goods_id'=>$goods_id))->select();
    }
    
}