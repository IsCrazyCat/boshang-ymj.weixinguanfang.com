<?php


class AppointdianpingpicsModel extends CommonModel{
    protected $pk   = 'pic_id';
    protected $tableName =  'appoint_dianping_pics';


    public function upload($dianping_id,$photos,$order_id){
        $dianping_id = (int)$dianping_id;
        $this->delete(array("where"=>array('dianping_id'=>$dianping_id)));
        foreach($photos as $val){
            $this->add(array('pic'=>$val,'dianping_id'=>$dianping_id,'order_id'=>$order_id));
        }
        return true;
    }

    public function getPics($dianping_id){
        $dianping_id = (int)$dianping_id;
        return $this->where(array('dianping_id'=>$dianping_id))->select();
    }

}