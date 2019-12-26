<?php

class FarmModel extends CommonModel{
    protected $pk   = 'farm_id';
    protected $tableName =  'farm';

    public function getFarmGroup() {
        return array(
            '1' => '朋友聚会',
            '2' => '学生活动',
            '3' => '老年养生',
            '4' => '亲子游玩',
            '5' => '家庭游玩',
            '6' => '公司组团',
            '7' => '情侣游玩',
        );
    }
    
    public function getFarmCate() {
        return array(
            '1' => '会议',
            '2' => '采摘',
            '3' => '垂钓',
            '4' => '烧烤',
            '5' => '登山',
            '6' => '温泉',
            '7' => '赏花',
        );
    }
    
    public function getPeople() {
        return array(
            '1' => '1-2人',
            '2' => '3-5人',
            '3' => '5-8人',
            '4' => '8-12人',
            '5' => '12人以上',
        );
    }
    
    public function getDays() {
        return array(
            '1' => '2天',
            '2' => '3天',
            '3' => '5天',
            '4' => '7天',
        );
    }
    
    public function getid($shop_id,$type) {
        if($type == 1 || !$type){
            $rs = D('Farmgroupattr');  //适合人群
        }else{
            $rs = D('Farmplayattr');   //能玩什么
        }
        if(!$shop_id){
            return false;
        }else if($res = $rs->where(array('shop_id'=>$shop_id))->select()){
            $Arrays = array();
            foreach($res as $k => $v){
                $Arrays[] = $v['attr_id'];
            }
            return $Arrays;
        }else{
            return false;
        }
    }
	
     
}