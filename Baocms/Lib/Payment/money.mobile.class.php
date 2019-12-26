<?php



class money{//余额支付
    
    public function  getCode($logs){
        
        return '<input type="button" class="button button-block bg-dot button-big" onclick="window.open(\''.U('user/member/pay',array('logs_id'=>$logs['logs_id'])).'\')" value=" 立刻支付 " />';
    }

    public function respond(){
        
    }
    
}