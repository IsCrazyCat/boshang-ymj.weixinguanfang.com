<?php
class GoodscateattrModel extends CommonModel
{
    protected $pk = 'attr_id';
    protected $tableName = 'goods_cate_attr';
    protected $token = 'goods_cate_attr';
    protected $orderby = array('orderby' => 'asc', 'attr_id' => 'asc');
    public function getAttrs($cate_id)
    {
        $items = $this->where(array('cate_id' => (int) $cate_id))->select();
        $return = array();
        foreach ($items as $val) {
            $return[$val['type']][$val['attr_id']] = $val;
        }
        return $return;
    }
}