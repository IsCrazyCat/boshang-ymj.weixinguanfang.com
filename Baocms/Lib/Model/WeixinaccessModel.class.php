<?php
class WeixinaccessModel extends CommonModel
{
    protected $pk = 'id';
    protected $tableName = 'weixin_access';
    public function getToken()
    {
        $data = $this->order('id desc')->find();
        if (empty($data)) {
            return false;
        }
        if ($data['expir_time'] - NOW_TIME <= 0) {
            return false;
        }
        return $data;
    }
    public function setToken($token)
    {
        $this->add(array('access_token' => $token, 'expir_time' => NOW_TIME + 7000,'create_time'=>NOW_TIME));
        return true;
    }
}