<?php
class ShopdianpingpicsModel extends CommonModel
{
    protected $pk = 'pic_id';
    protected $tableName = 'shop_dianping_pics';
    public function upload($dianping_id, $shop_id, $photos)
    {
        $shop_id = (int) $shop_id;
        $dianping_id = (int) $dianping_id;
        $this->delete(array("where" => array('dianping_id' => $dianping_id)));
        foreach ($photos as $val) {
            $this->add(array('dianping_id' => $dianping_id, 'pic' => $val, 'shop_id' => $shop_id));
        }
        return true;
    }
    public function getPics($dianping_id)
    {
        $dianping_id = (int) $dianping_id;
        return $this->where(array('dianping_id' => $dianping_id))->select();
    }
}