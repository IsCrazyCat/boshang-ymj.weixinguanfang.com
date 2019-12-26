<?php
class AwardModel extends CommonModel{
    protected $pk   = 'award_id';
    protected $tableName =  'award';
    
    protected $typeCfg = array(
        'lottery'   => '抽奖',
        'scratch'   => '刮刮卡',
        'shark'     => '摇一摇',
    );
    
    public function getCfg(){
        
        return $this->typeCfg;
    }
    
    
}