<?php
class AreaModel extends CommonModel{
    protected $pk   = 'area_id';
    protected $tableName =  'area';
    protected $token = 'area';
    protected $orderby = array('orderby'=>'asc');
   
    public function setToken($token)
    {
        $this->token = $token;
    }
 
}