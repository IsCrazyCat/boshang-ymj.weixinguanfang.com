<?php
class BusinessModel extends CommonModel{
    protected $pk   = 'business_id';
    protected $tableName =  'business';
    protected $token = 'business';
    protected $orderby = array('orderby'=>'asc');

     public   function _format($data){
        static $area = null;
        if($area == null){
            $area = D('Area')->fetchAll();
        }
        $data['area_name'] = $area[$data['area_id']]['area_name'];
        return $data;
    }
    
    public function setToken($token)
    {
        $this->token = $token;
    }
}