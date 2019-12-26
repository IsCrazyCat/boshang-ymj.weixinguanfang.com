<?php

class HotelroomModel extends CommonModel{
    protected $pk   = 'room_id';
    protected $tableName =  'hotel_room';
    
    
    public function getRoomType(){
        return array(
            1 => '双床房',
            2 => '单人房',
            3 => '大床房',
            4 => '无烟房',
        );
    }
     
}