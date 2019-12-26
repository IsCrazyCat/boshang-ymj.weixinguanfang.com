<?php
class LifeservicedianpingpicsModel extends CommonModel
{
    protected $pk = 'pic_id';
    protected $tableName = 'lifeservice_dianping_pics';
    public function upload($id, $photos)
    {
        $id = (int) $id;
        $this->delete(array("where" => array('id' => $id)));
        foreach ($photos as $val) {
            $this->add(array('pic' => $val, 'id' => $id));
        }
        return true;
    }
    public function getPics($id)
    {
        $id = (int) $id;
        return $this->where(array('id' => $id))->select();
    }
}