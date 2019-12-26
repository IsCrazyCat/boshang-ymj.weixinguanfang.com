<?php

class HotelModel extends CommonModel{
    protected $pk   = 'hotel_id';
    protected $tableName =  'hotel';
    
    
    public function getHotelCate() {
        return array(
            '1' => '商务型',
            '2' => '度假型',
            '3' => '长住型',
            '4' => '会议型',
            '5' => '观光型',
            '6' => '经济型',
            '7' => '连锁',
            '8' => '公寓式',
        );
    }

    
    public function getHotelStar() {
        return array(
            '1' => '一星酒店',
            '2' => '二星酒店',
            '3' => '三星酒店',
            '4' => '四星酒店',
            '5' => '五星酒店',
        );
    }
     
}