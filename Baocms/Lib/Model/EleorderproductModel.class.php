<?php
class EleorderproductModel extends CommonModel
{
    protected $pk = 'id';
    protected $tableName = 'ele_order_product';
    public function updateByOrderId($order_id)
    {
        $order_id = (int) $order_id;
        $product = $this->where(array('order_id' => $order_id))->select();
        $ids = array();
        foreach ($product as $val) {
            $ids[$val['product_id']] = $val['product_id'];
        }
        $idsstr = join(',', $ids);
        $month = date('Ym', NOW_TIME);
        $datas = $this->query(" select  product_id ,count(1) as num from " . $this->getTableName() . " where product_id IN({$idsstr}) AND month='{$month}' group by product_id ");
        $datas2 = $this->query(" select  product_id ,count(1) as num from " . $this->getTableName() . " where product_id IN({$idsstr}) group by product_id ");
        foreach ($datas as $val) {
            D('Eleproduct')->save(array('product_id' => $val['product_id'], 'month_num' => $val['num']));
        }
        foreach ($datas2 as $val) {
            D('Eleproduct')->save(array('product_id' => $val['product_id'], 'sold_num' => $val['num']));
        }
        return true;
    }
}