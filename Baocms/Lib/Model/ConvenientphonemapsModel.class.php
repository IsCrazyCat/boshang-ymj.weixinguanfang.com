<?php
class ConvenientphonemapsModel extends CommonModel {

    protected $pk = 'phone_id';
    protected $tableName = 'convenient_phone_maps';

    public function getCommunity($phone_id) {
        $phone_id = (int) $phone_id;
        return $this->where(array('phone_id' => $phone_id))->select();
    }

}
