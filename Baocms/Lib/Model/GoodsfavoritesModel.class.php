<?php
class GoodsfavoritesModel extends CommonModel{
    protected $pk = 'favorites_id';
    protected $tableName = 'goods_favorites';
	
    public function check($goods_id, $user_id){
        $data = $this->find(array('where' => array('goods_id' => (int) $goods_id, 'user_id' => (int) $user_id)));
        return $this->_format($data);
    }
}