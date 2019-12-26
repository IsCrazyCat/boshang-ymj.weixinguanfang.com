<?php
class NearAction extends CommonAction{
    public function index(){
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');
        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }
        if (!empty($this->uid)) {
            if (empty($lat) || empty($lng)) {
                $usrdata = D('Users')->find($this->uid);
                $lat = $usrdata['lat'];
                $lng = $usrdata['lng'];
            }
        }
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        $place = cookie('place');
        if (empty($place)) {
            $place = $this->getArea($lat, $lng);
            cookie('place', $place);
        }
        $type = (int) $this->_param('type');
        $this->assign('type', $type);
        $this->assign('place', $place);
        $this->display();
    }
    /* 通过接口将坐标转地理位置 */
    function getArea($lat, $lng){
        $url = 'http://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $arr = file_get_contents($url);
        $arr = json_decode($arr);
        $place = $pois = $po = array();
        foreach ($arr->result->pois as $value) {
            $po['name'] = $value->name;
            $po['addr'] = $value->addr;
            $pois[] = $po;
        }
        $place['formatted_address'] = $arr->result->formatted_address;
		$place['city'] = $arr -> result -> addressComponent -> city;
		$place['district'] = $arr -> result -> addressComponent -> district;
        $place['pois'] = $pois;
        return $place;
    }
    /**
     * 腾讯地图坐标转百度地图坐标
     * @param [String] $lat 腾讯地图坐标的纬度
     * @param [String] $lng 腾讯地图坐标的经度
     * @return [Array] 返回记录纬度经度的数组
     */
    function ErroToBd($lat, $lng)
    {
        $x_pi = 3.141592653589793 * 3000.0 / 180.0;
        $x = $lng;
        $y = $lat;
        $z = sqrt($x * $x + $y * $y) + 2.0E-5 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 3.0E-6 * cos($x * $x_pi);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return array('lat' => $bd_lat, 'lng' => $bd_lon);
    }
    /*对象转换为数组*/
    function object_array($array)
    {
        if (is_object($array)) {
            $array = (array) $array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = object_array($value);
            }
        }
        return $array;
    }
    //重复数组
    function a_array_unique($array)
    {
        $out = array();
        foreach ($array as $key => $value) {
            if (!in_array($value, $out)) {
                $out[$key] = $value;
            }
        }
        return $out;
    }
    //坐标范围
    function returnSquarePoint($lng, $lat, $distance)
    {
        $dlng = 2 * asin(sin($distance / (2 * 6378.2)) / cos(deg2rad($lat)));
        $dlng = rad2deg($dlng);
        $dlat = $distance / 6378.2;
        $dlat = rad2deg($dlat);
        return array('left-top' => array('lat' => $lat + $dlat, 'lng' => $lng - $dlng), 'right-top' => array('lat' => $lat + $dlat, 'lng' => $lng + $dlng), 'left-bottom' => array('lat' => $lat - $dlat, 'lng' => $lng - $dlng), 'right-bottom' => array('lat' => $lat - $dlat, 'lng' => $lng + $dlng));
    }
    //偏移换算
    function placeToBaidu($lng, $lat)
    {
        $p = 3.141592653589793 * 6378.2 / 360.0;
        $x = $lng;
        $y = $lat;
        $z = sqrt($x * $x + $y * $y) + 2.0E-5 * sin($y * $p);
        $theta = atan2($y, $x) + 3.0E-6 * cos($x * $p);
        $bd_lng = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return array('lng' => $bd_lng, 'lat' => $bd_lat);
    }
    public function dingwei(){
        $lat = cookie('lat');
        $lng = cookie('lng');
        if (cookie('localed') != 2 || empty($lat) || empty($lng)) {
            $local = array($this->_param('lat'), $this->_param('lng'));
            cookie('lat_ok', $local[0], 3600);
            cookie('lng_ok', $local[1], 3600);
            cookie('lat', $local[0], 3600);
            cookie('lng', $local[1], 3600);
            $addr = $this->getArea($local[0], $local[1]);
            cookie('addr', $addr, 3600);
            if (!empty($addr)) {
                cookie('localed', 2);
            }
        }
        echo '1';
    }
	//首页城市定位  可定位至4级区域，区/县
		public function csdwpl() {
		$lat = $this->_param('lat');
		$lng = $this->_param('lng');
		cookie('lat_ok', $lat, 3600);
		cookie('lng_ok', $lng, 3600);
		cookie('lat', $lat, 3600);
		cookie('lng', $lng, 3600);
		$addr = $this -> getArea($lat, $lng);
		cookie('addr', $addr, 3600);
		if (!empty($addr)) {
			cookie('localed', 2);
		}
		$city = mb_substr($addr['city'], 0, -1, 'utf-8');
		$district = mb_substr($addr['district'], 0, -1, 'utf-8');
		$wdw = $city .$district;
		$city = D('Pinyin') -> pinyin($city);
		$district = D('Pinyin') -> pinyin($district);
		$xiancheng = D('City') -> where(array('pinyin' => $district,'is_open' => 1)) -> find();
		$chengshi = D('City') -> where(array('pinyin' => $city,'is_open' => 1)) -> find();
		$mcityid = cookie('city_id');
		$cityop = cookie('cityop');
		if (!empty($mcityid)) {
			if ($mcityid == $xiancheng['city_id'] || $mcityid == $chengshi['city_id'] || $cityop == 1) {
				$cityid = 9999;
				$outArr = array('cityid' => $cityid); //当默认城市和当前获取坐标城市一样时，不进行弹出提示
			} else {
				$mcity = D('City') -> where(array('city_id' => $mcityid)) -> getField('name');
				if (!empty($xiancheng)) {
					$cityid = $xiancheng['city_id'];
					$city = $xiancheng['name'];
					$outArr = array('moren'=>1,'city' => $city, 'cityid' => $cityid, 'mcity' => $mcity, 'mcityid' => $mcityid);
				} elseif (!empty($chengshi)) {
					$cityid = $chengshi['city_id'];
					$city = $chengshi['name'];
					$outArr = array('moren'=>1,'city' => $city, 'cityid' => $cityid, 'mcity' => $mcity, 'mcityid' => $mcityid);
				}
			}
		} else {
			if (!empty($xiancheng)) {
				$cityid = $xiancheng['city_id'];
				$city = $xiancheng['name'];
				$outArr = array('city' => $city, 'cityid' => $cityid);
			} elseif (!empty($chengshi)) {
				$cityid = $chengshi['city_id'];
				$city = $chengshi['name'];
				$outArr = array('city' => $city, 'cityid' => $cityid);
			} else {
				$cityid = 0;
				$city = $wdw;
				$outArr = array('city' => $city, 'cityid' => $cityid);
			}
		}
		echo json_encode($outArr);
	}
    public function address(){
        $addr = cookie('addr');
        echo $addr['formatted_address'];
    }
    public function reset(){
        $local = array($this->_param('lat'), $this->_param('lng'));
        cookie('lat_ok', $local['0'], 3600);
        cookie('lng_ok', $local['1'], 3600);
        cookie('lat', $local['0'], 3600);
        cookie('lng', $local['1'], 3600);
        $addr = $this->getArea($local['0'], $local['1']);
        cookie('addr', $addr, 3600);
        if (!empty($addr)) {
            cookie('localed', 1);
        }
        echo $addr['formatted_address'];
    }
    public function search()
    {
        $keyword = urlencode($this->_param('keyword', 'htmlspecialchars'));
        $type = (int) $this->_param('type');
        $this->assign('type', $type);
        $this->assign('nextpage', LinkTo('near/loaddata', array('type' => $type, 'keyword' => $keyword, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('keyword', urldecode($keyword));
        $this->display();
    }
    public function loaddata(){
        set_time_limit(0);
        $lat = cookie('lat');
        $lng = cookie('lng');
        if (empty($lat) || empty($lng)) {
            die;
        }
        $keyword = $this->_param('keyword');
        $type = (int) $this->_param('type');
        $pois_city = D('City')->find($this->city_id);
        import('ORG.Util.Page');
        //验证城市信息
        if (session('city_code') == '') {
            $place = D('Cloud')->GetAddress($lat, $lng);
            $place = json_decode($place);
            $city_code = $place->result->addressComponent->city;
            session('city_code', $city_code);
        } else {
            $city_code = session('city_code');
        }
        // 获取周边信息
        $key = urldecode($keyword);
        $arr = D('Cloud')->NearData($lat, $lng, $key, $p);
        $num = intval($arr->status);
        $result = $arr->results;

        if ($num == 0) {
            foreach ($result as $value) {
                $data = array();
                $data['uid'] = $value->uid;
                $data['name'] = $value->name;
                $data['city_id'] = $pois_city['city_id'];
                $data['type'] = $type;
                $data['lat'] = $value->location->lat;
                $data['lng'] = $value->location->lng;
                $data['telephone'] = $value->telephone;
                $data['address'] = $value->address;
                $data['tag'] = $value->detail_info->tag;
                $data['orderby'] = 1000;
                $data['create_time'] = NOW_TIME;
                //入库到小区
                $communitys = array();
                $communitys['uid'] = $value->uid;
                $communitys['name'] = $value->name;
                $communitys['city_id'] = $pois_city['city_id'];
                $communitys['type'] = $type;
                $communitys['lat'] = $value->location->lat;
                $communitys['lng'] = $value->location->lng;
                if (!empty($value->telephone)) {
                    $communitys['tel'] = $value->telephone;
                } else {
                    $communitys['tel'] = $this->_CONFIG['site']['tel'];
                }
                $communitys['addr'] = $value->address;
                $communitys['orderby'] = 100;
                $communitys['create_time'] = NOW_TIME;
                //入库到商家库
                $shops = array();
                $shops['uid'] = $value->uid;
                $shops['shop_name'] = $value->name;
                $shops['city_id'] = $pois_city['city_id'];
                $shops['type'] = $type;
                $shops['lat'] = $value->location->lat;
                $shops['lng'] = $value->location->lng;
                if (!empty($value->telephone)) {
                    $shops['tel'] = $value->telephone;
                } else {
                    $shops['tel'] = $this->_CONFIG['site']['tel'];
                }
                $shops['addr'] = $value->address;
                $shops['tag'] = $value->detail_info->tag;
                $shops['orderby'] = 100;
                $shops['create_time'] = NOW_TIME;
                //配置文件
                $citys_name = D('City')->find($this->city_id);
                $collects_open = $this->_CONFIG['collects']['open'];
                $collects_community = $this->_CONFIG['collects']['community'];
                $collects_shop = $this->_CONFIG['collects']['shop'];
                if ($collects_open == 1) {
                    if ($citys_name['name'] == $city_code) {
                        if ($type == $collects_community) {
                            $Community = D('Community')->where(array('uid' => $value->uid))->find();
                            if (!empty($Community['community_id'])) {
                            } else {
                                D('Community')->add($communitys);
                            }
                        } elseif ($type == $collects_shop) {
                            $shop = D('Shop')->where(array('uid' => $value->uid))->find();
                            if (!empty($shop['shop_id'])) {
                            } else {
                                D('Shop')->add($shops);
                            }
                        } else {
                            $pois = D('Near')->where(array('uid' => $value->uid))->find();
                            if (!empty($pois['pois_id'])) {
                                $data['pois_id'] = $pois['pois_id'];
                                $creat_time = intval($pois['create_time']);
                                $time_plus = NOW_TIME - $creat_time;
                                if ($time_plus > 1314871 && $pois['is_lock'] == 0) {
                                    D('Near')->save($data);
                                }
                            } else {
                                D('Near')->add($data);
                            }
                        }
                    }
                }
                $poi[] = $data;
            }
        }
        $Page = new Page(count($poi), 10);
        $show = $Page->show();
        // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->assign('keyword', urldecode($keyword));
        $this->assign('poi', $poi);
        $this->assign('page', $show);
        $this->display();
    }
    public function detail($id){
        //获取用户自定坐标
        $local = $this->location();
        $lat = $local['lat'];
        $lng = $local['lng'];
        //判断查询类型
        if (is_numeric($id)) {
            $detail = D('Near')->where(array('pois_id' => $id))->find();
        } else {
            $detail = D('Near')->where(array('uid' => $id))->find();
        }
        //如果是入驻商家
        if (!empty($detail['shop_id'])) {
            $shop = D('Shop')->find($detail['shop_id']);
            $this->assign('shop', $shop);
        }
        //本地没有数据到远程获取
        if (empty($detail) && !is_numeric($id)) {
            $bdurl = 'http://api.map.baidu.com/place/v2/detail?uid=' . $id . '&ak=C9613fa45f450daa331d85184c920119&output=json&scope=2';
            $bdtxt = file_get_contents($bdurl);
            $bdarr = json_decode($bdtxt);
            $detail['uid'] = $bdarr->result->uid;
            $detail['name'] = $bdarr->result->name;
            $detail['type'] = $bdarr->result->detail_info->type;
            $detail['lat'] = $bdarr->result->location->lat;
            $detail['lng'] = $bdarr->result->location->lng;
            $detail['telephone'] = $bdarr->result->telephone;
            $detail['address'] = $bdarr->result->address;
            $detail['tag'] = $bdarr->result->detail_info->tag;
        }
        $distance = getDistanceCN($detail['lat'], $detail['lng'], $lat, $lng);
        $this->assign('distance', $distance);
        $this->assign('detail', $detail);
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->display();
    }
    public function gps($id){
        //获取用户自定坐标
        $local = $this->location();
        $lat = $local['lat'];
        $lng = $local['lng'];
        //判断查询类型
        if (is_numeric($id)) {
            $detail = D('Near')->where(array('pois_id' => $id))->find();
        } else {
            $detail = D('Near')->where(array('uid' => $id))->find();
        }
        //本地没有数据到远程获取
        if (empty($detail) && !is_numeric($id)) {
            $bdurl = 'http://api.map.baidu.com/place/v2/detail?uid=' . $id . '&ak=C9613fa45f450daa331d85184c920119&output=json&scope=2';
            $bdtxt = file_get_contents($bdurl);
            $bdarr = json_decode($bdtxt);
            $detail['uid'] = $bdarr->result->uid;
            $detail['name'] = $bdarr->result->name;
            $detail['type'] = $bdarr->result->detail_info->type;
            $detail['lat'] = $bdarr->result->location->lat;
            $detail['lng'] = $bdarr->result->location->lng;
            $detail['telephone'] = $bdarr->result->telephone;
            $detail['address'] = $bdarr->result->address;
            $detail['tag'] = $bdarr->result->detail_info->tag;
        }
        $this->assign('detail', $detail);
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->display();
    }
    public function select(){
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');
        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }
        if (!empty($this->uid)) {
            if (empty($lat) || empty($lng)) {
                $usrdata = D('Users')->find($this->uid);
                $lat = $usrdata['lat'];
                $lng = $usrdata['lng'];
            }
        }
        //获取系统默认坐标
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        //获取地理位置
        $place = cookie('place');
        if (empty($place)) {
            $place = getArea($lat, $lng);
            cookie('place', $place);
        }
        //获取地址推荐
        $url = 'http://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $json = file_get_contents($url);
        $geo = object_array(json_decode($json));
        $this->assign('geo', $geo);
        $this->assign('place', $place);
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->display();
    }
    public function load(){
        $lat = $this->_param('lat', 'htmlspecialchars');
        $lng = $this->_param('lng', 'htmlspecialchars');
        //获取地址推荐
        $url = 'http://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $json = file_get_contents($url);
        $geo = object_array(json_decode($json));
        $this->assign('geo', $geo);
        $this->display();
    }
    public function selected(){
        $lat = $this->_param('lat', 'htmlspecialchars');
        $lng = $this->_param('lng', 'htmlspecialchars');
        cookie('lat_ok', null);
        cookie('lng_ok', null);
        cookie('lat_ok', $lat);
        cookie('lng_ok', $lng);
        $this->fengmiAlert('您的位置已经重置，请返回继续浏览！', U('index/index'));
    }
    public function location(){
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');
        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }
        if (!empty($this->uid)) {
            if (empty($lat) || empty($lng)) {
                $usrdata = D('Users')->find($this->uid);
                $lat = $usrdata['lat'];
                $lng = $usrdata['lng'];
            }
        }
        //获取系统默认坐标
        if (empty($lat) || empty($lng)) {
            $lat = $this->_CONFIG['site']['lat'];
            $lng = $this->_CONFIG['site']['lng'];
        }
        $arr = array('lat' => $lat, 'lng' => $lng);
        return $arr;
    }
	
	public function get_location() {
		$lat = I('lat', '', 'intval,trim');
		$lng = I('lng', '', 'intval,trim');
		cookie('lat_ok', $lat, 3600);
		cookie('lng_ok', $lng, 3600);
		cookie('lat', $lat, 3600);
		cookie('lng', $lng, 3600);
		$addr = $this -> getArea($lat, $lng);
		cookie('addr', $addr, 3600);
		if (!empty($addr)) {
			cookie('localed', 2);
		}
		$city = mb_substr($addr['city'], 0, -1, 'utf-8');
		$district = mb_substr($addr['district'], 0, -1, 'utf-8');
		$xdgsg = $addr['formatted_address'];
		$xiancheng = D('City') -> where(array('name' => array('LIKE', '%' . $district . '%'))) -> find();
		$chengshi = D('City') -> where(array('name' => array('LIKE', '%' . $city . '%'))) -> find();
		$mcityid = cookie('city_id');
		if (!empty($mcityid)) {
			if ($mcityid == $xiancheng['city_id'] || $mcityid == $chengshi['city_id']) {
				$cityid = 9999;
				$outArr = array('cityid' => $cityid); //当默认城市和当前获取坐标城市一样时，不进行弹出提示
			} else {
				$mcity = D('City') -> where(array('city_id' => $mcityid)) -> getField('name');
				if (!empty($xiancheng)) {
					$cityid = $xiancheng['city_id'];
					$city = $xiancheng['name'];
					$outArr = array('moren'=>1,'city' => $city, 'cityid' => $cityid, 'mcity' => $mcity, 'mcityid' => $mcityid);
				} elseif (!empty($chengshi)) {
					$cityid = $chengshi['city_id'];
					$city = $chengshi['name'];
					$outArr = array('moren'=>1,'city' => $city, 'cityid' => $cityid, 'mcity' => $mcity, 'mcityid' => $mcityid);
				}
			}
		} else {
			if (!empty($xiancheng)) {
				$cityid = $xiancheng['city_id'];
				$city = $xiancheng['name'];
				$outArr = array('city' => $city, 'cityid' => $cityid);
			} elseif (!empty($chengshi)) {
				$cityid = $chengshi['city_id'];
				$city = $chengshi['name'];
				$outArr = array('city' => $city, 'cityid' => $cityid);
			} else {
				$cityid = 0;
				$city = $city;
				$outArr = array('city' => $city, 'cityid' => $cityid);
			}
		}
		echo json_encode($outArr);
	}

}