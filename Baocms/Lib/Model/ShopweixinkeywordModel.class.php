<?php
class ShopweixinkeywordModel extends CommonModel
{
    protected $pk = 'keyword_id';
    protected $tableName = 'shop_weixin_keyword';
    public function checkKeyword($shop_id, $keyword)
    {
        return $this->find(array('where' => array('shop_id' => $shop_id, 'keyword' => $keyword)));
    }
}