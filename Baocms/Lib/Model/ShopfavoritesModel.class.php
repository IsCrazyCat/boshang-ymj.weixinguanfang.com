<?php
class ShopfavoritesModel extends CommonModel
{
    protected $pk = 'favorites_id';
    protected $tableName = 'shop_favorites';
    public function check($shop_id, $user_id)
    {
        $data = $this->find(array('where' => array('shop_id' => (int) $shop_id, 'user_id' => (int) $user_id)));
        return $this->_format($data);
    }
}