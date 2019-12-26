<?php

class PincheModel extends CommonModel {

    protected $pk = 'pinche_id';
    protected $tableName = 'pinche';

    public function getPincheCate() {
        return array(
            '1' => '车找人',
            '2' => '人找车',
            '3' => '车找货',
            '4' => '货找车',
        );
    }

  
    

}
