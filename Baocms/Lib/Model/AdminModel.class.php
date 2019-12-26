<?php
class AdminModel extends CommonModel{
    protected $pk   = 'admin_id';
    protected $tableName =  'admin';
    
     public function getAdminByUsername($username){
        $data = $this->find(array('where'=>array('username'=>$username)));
        return $this->_format($data);
    }
    
    public  function _format($data){
        static  $roles;
        if(empty($roles)){
			$roles = D('Role')->select();
		}
        if(!empty($data)){
			$data['role_name'] = $roles[$data['role_id']-1]['role_name'];   
		}  
        return $data;
    }
}