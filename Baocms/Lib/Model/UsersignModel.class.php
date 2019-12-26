<?php
class  UsersignModel extends CommonModel{
     protected $pk   = 'user_id';
     protected $tableName =  'user_sign';
    
     public function getSign($user_id,$integral = false){
         $user_id = (int)$user_id;
         if(!$data = $this->find($user_id)){
             $data = array(
                 'user_id'      => $user_id,
                 'day'          => 0,
                 'last_time'    => NOW_TIME - 86400,
                 'is_first'     => 1
             );
             $this->add($data);
         }
         if($integral!==false){ //返回明日登录积分 及 今天是否登录的状态
             $day=$data['day'] == 0 ? $data['day'] + 2 : $data['day']+1;
             if($day > 1){
                 $integral+=$day; //加上连续登陆的天数
             }
             $data['integral'] = $integral;
             $lastdate = date('Y-m-d',$data['last_time']);
           
             if($lastdate  == TODAY){ 
                 $data['is_sign'] = 1;
             }else{
                 $data['is_sign'] = 0;
             }
         }
         return $data;
     }
     
     public function sign($user_id,$integral,$firstintegral = 0){ 
         $user_id = (int)$user_id;
         $integral = (int) $integral;
         $data = $this->getSign($user_id);
         $lastdate = date('Y-m-d',$data['last_time']);
         if($lastdate < TODAY){ 
             if(NOW_TIME - $data['last_time'] > 86400){//隔天了
                  $data['day'] =  1;
             }else{
                  $data['day']+=1;
             }
             if($data['day'] > 1){
                 $integral+=$data['day']; //加上连续登陆的天数
             }
             $is_first = false;
             if($data['is_first']){
                 $is_first = true;
                 $data['is_first'] = 0;
             }
             $data['last_time'] = NOW_TIME;
             if($this->save($data)){
                 $return = $integral;
                if($is_first){
                   D('Users')->addIntegral($user_id,$firstintegral,'首次签到');     
                   $return += $firstintegral;
                }
                D('Users')->addIntegral($user_id,$integral,TODAY.'手机签到');             
                return $return;
             }
             return false;
         }
         return false;
     }
     
     
    
    
}