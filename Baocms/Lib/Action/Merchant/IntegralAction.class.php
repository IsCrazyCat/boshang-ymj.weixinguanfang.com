<?php

class IntegralAction extends CommonAction{
    
    public function index(){
        if($this->isPost()){
            $num = (int)$this->_post('num');
            if($num <=0){
                $this->baoError('要兑换的数量不能为空！');
            }
            //if (!D('Lock')->lock($this->uid)) { //上锁
                //$this->baoError('服务器繁忙，1分钟后再试');
           // }
            if($this->member['gold'] < $num){
              //  D('Lock')->unlock();
                $this->baoError('账户余额不足');
            }
            if(D('Users')->addGold($this->uid,-$num,'金块兑换积分')){
                D('Users')->addIntegral($this->uid,$num*100,'金块兑换积分');          
            }            
           // D('Lock')->unlock();
            $this->baoSuccess('兑换积分成功！',U('integral/index')); 
        }else{
             $this->display();
        }
    }
    
    
    public function used(){
        if($this->isPost()){
            $card_num = $this->_post('card_num',  'htmlspecialchars');
            $card = D('Usercard')->checkCard($card_num);
            if(empty($card)){
                $this->baoError('该会员卡可能不存在或者没有激活！');
            }
            if(empty($card['user_id'])){
                $this->baoErrorJump('该会员卡未绑定会员！',U('integral/bind',array('card_num'=>$card_num)));
            }else{
                $this->baoSuccess('查询到对应用户,请赠送积分',U('integral/add',array('uid'=>$card['user_id'])));
            }
            
        }else{
            $this->display();
        }     
    }
    
    
    public function add($uid){
        $uid = (int)$uid;
        if(empty($uid)){
            $this->error('用户不存在！');
        }
        if(!$user = D('Users')->find($uid)){
            $this->error('用户不存在');
        }
        if($user['closed'] == 1){
            $this->error('用户不存在');
        }
        
        if($this->isPost()){
            $num = (int)$this->_post('num');
            if($num <=0){
                $this->baoError('数量不正确');
            }
           // if (!D('Lock')->lock($this->uid)) { //上锁
                //$this->baoError('服务器繁忙，1分钟后再试');
           // }
            if($this->member['integral'] < $num){
               // D('Lock')->unlock();
                $this->baoError('账户余额不足');
            }
            if(D('Users')->addIntegral($this->uid,-$num,'赠送会员积分！')){
                D('Users')->addIntegral($uid,$num,'获得“'.$this->shop['shop_name'].'”商家赠送积分');          
            }            
          //  D('Lock')->unlock();
            $this->baoSuccess('赠送会员积分成功！',U('integral/used')); 
            
        }else{
            $this->assign('detail',$user);
            $this->assign('uid',$uid);
            $this->display();
        }
        
    }
    
    
    public function bind(){
        $card_num = $this->_param('card_num',  'htmlspecialchars');
        if(empty($card_num)){
            $this->error('请输入卡号',U('integral/used'));
        }
        $card = D('Usercard')->checkCard($card_num);
        if(empty($card)){
            $this->error('该会员卡可能不存在或者没有激活');
        }
        if($this->isPost()){
            if($card['user_id']){
                $this->baoError('该卡绑定的是其他会员！');
            }
            $username = $this->_post('username','htmlspecialchars');
            $user_id = (int)  $this->_post('user_id');
            $user = array();
            if(!empty($username)){
                $user = D('Users')->getUserByAccount($username);
            }
            if(!empty($user_id)){
                $user = D('Users')->find($user_id);
            }
            if(empty($user)){
                $this->baoError('该帐号不存在');
            }
            var_dump($user);
            if($user['closed']==1){
                 $this->baoError('该帐号不存在');
            }
            $card['user_id'] = $user['user_id'];
            D('Usercard')->save($card);
            $this->baoSuccess('绑定成功',U('integral/add',array('uid'=>$card['user_id'])));
        }else{
            $this->assign('card_num',$card_num);
            $this->display();
        }
    }
    
    
}