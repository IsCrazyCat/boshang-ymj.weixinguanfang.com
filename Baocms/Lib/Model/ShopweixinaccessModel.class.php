<?php
class ShopweixinaccessModel extends CommonModel
{
    protected $pk = 'shop_id';
    protected $tableName = 'shop_weixin_access';
    public function getToken($shop_id)
    {
        $data = $this->find($shop_id);
        if (empty($data)) {
            return false;
        }
        if ($data['expir_time'] - NOW_TIME <= 0) {
            return false;
        }
        return $data['access_token'];
    }
    public function setToken($shop_id, $token)
    {
        if (!$this->find($shop_id)) {
            $this->add(array('shop_id' => $shop_id, 'access_token' => $token, 'expir_time' => NOW_TIME + 7000));
        } else {
            $this->save(array('shop_id' => $shop_id, 'access_token' => $token, 'expir_time' => NOW_TIME + 7000));
        }
        return true;
    }
}