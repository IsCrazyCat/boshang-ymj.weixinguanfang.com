<?php
class CloudModel extends CommonModel{
	private $curl = null;
    public function __construct() {
        import("@/Net.Curl");
        $this->curl = new Curl();
    }
	

    public function GetLocation($lat,$lng) { //通过云端校准坐标
		$str = file_get_contents($url);
        return $str;
    }
	
	
    public function GetAddress($lat,$lng) { //通过云端校准以坐标获取详细地址
        $url ='http://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location='.$lat.','.$lng.'&output=json&pois=1';
		$str = file_get_contents($url);
        return $str;
    }
	
	
	
    public function NearData($lat,$lng,$key,$p) { //通过云端获取POIS数据
        $url = 'http://api.map.baidu.com/place/v2/search?query='.$key.'&location='.$lat.','.$lng.'&radius=2000&output=json&ak=C9613fa45f450daa331d85184c920119';
		$str = file_get_contents($url);
		$arr = json_decode($str);
		return $arr;
    }
		
}