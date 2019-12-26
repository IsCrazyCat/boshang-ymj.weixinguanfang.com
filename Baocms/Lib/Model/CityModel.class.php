<?php
class CityModel extends CommonModel{
    protected $pk = 'city_id';
    protected $tableName = 'city';
    protected $token = 'city';
    protected $orderby = array('orderby' => 'asc');
    public function setToken($token)
    {
        $this->token = $token;
    }
	public function check_city_domain($city_id,$NOWHOST,$BAO_DOMAIN){
        $cityid = D('city')->where(array('city_id' => $city_id))->Field('pinyin,domain')->select();
        if ($cityid[0]['domain'] == '1' && $NOWHOST != $cityid[0]['pinyin']) {
			$url = "http://" . $cityid[0]['pinyin'] . '.' . $BAO_DOMAIN . $_SERVER['REQUEST_URI'];
            return $url;
        }
		return false;
	}
}