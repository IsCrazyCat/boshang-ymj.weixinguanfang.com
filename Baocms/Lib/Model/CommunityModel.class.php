<?php
class CommunityModel extends CommonModel{
    protected $pk = 'community_id';
    protected $tableName = 'community';
    protected $orderby = array('orderby' => 'asc');
    public function _format($data){
        static $area = null;
        if ($area == null) {
            $area = D('Area')->fetchAll();
        }
        $data['area_name'] = $area[$data['area_id']]['area_name'];
        return $data;
    }
	//检测会员是不是在管理了
	public function check_user_id_occupy($user_id){
        if($this->where(array('user_id'=>$user_id))->find()){
			return false;
		}else{
			return true;	
	    }
    }
}