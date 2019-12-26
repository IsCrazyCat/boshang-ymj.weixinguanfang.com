<?php


class UserweixinModel extends CommonModel{
    protected $pk   = 'wx_id';
    protected $tableName =  'user_weixin';


	public function detail_by_unionid()
	{

		if($row = D('Userweixin')->query("SELECT w.*,m.* FROM bao_user_weixin w LEFT JOIN bao_users m ON m.user_id=w.user_id WHERE w.unionid='$unionid'")){
            return $row;
        }
        return false;
	}

	public function detail_by_openid($openid)
    {
        if($row = D('Userweixin')->query("SELECT w.*,m.* FROM bao_user_weixin w LEFT JOIN bao_users m ON  m.user_id=w.user_id WHERE w.openid='$openid'")){
            return $row;
        }
        return false;
    }
    
}