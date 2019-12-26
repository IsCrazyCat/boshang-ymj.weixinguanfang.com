<?php
class ShopapplyModel extends CommonModel
{
    protected $pk = 'apply_id';
    protected $tableName = 'shop_apply';
    public function _format($data)
    {
        static $cates = null;
        if ($cates == null) {
            $cates = D('Shopcate')->fetchAll();
        }
        $data['cate_name'] = $cates[$data['cate_id']]['cate_name'];
        return $data;
    }
}