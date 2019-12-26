<?php
class  AwardshareModel  extends  CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'award_share';
    
    public function  getdata($award_id){
        $award_id = (int)$award_id;
        $ip = get_client_ip();
        if(!$data=$this->find(array('where'=>array('award_id'=>$award_id,'ip'=>$ip)))){
            $data = array(
                'award_id'=>$award_id,
                'ip'      => $ip,
                'is_used' => 0,
                'num' => 0,
            );
            $data['id']=$this->add($data);
        }
        return $data;
    }

	public function get_count()
	{
		$count = $this->count();
		return $count;
	}
    
    
}