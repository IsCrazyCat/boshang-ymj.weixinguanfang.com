<?php

class CrowdproveModel extends CommonModel{
    protected $pk   = 'prove_id';
    protected $tableName =  'crowd_prove';
	
	 public function type() {
        return array(
            '1' => '同事',
            '2' => '朋友',
            '3' => '亲戚',
            '4' => '家人',
            '5' => '上下属',
            '6' => '其他',
        );
    }
    
}