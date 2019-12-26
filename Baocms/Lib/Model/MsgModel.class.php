<?php
class MsgModel extends CommonModel{
    protected $pk   = 'msg_id';
    protected $tableName =  'msg';
    
    protected $types = array(
        'gift'      => '红包礼物',
        'movie'     => '官方动态',
        'message'   => '个人消息',
        'coupon'    => '套餐优惠',
    );
    
    public function getType(){
        return $this->types;
    }
	
	 public function getMsgCate() {
        return array(
            '1' => '会员',
            '2' => '商家',
            '3' => '分站管理员',
            '4' => '物业小区管理员',
			'5' => '物流配送员',
			'6' => '商家员工',
			'7' => '智慧乡村管理员',
        );
    }
    
}