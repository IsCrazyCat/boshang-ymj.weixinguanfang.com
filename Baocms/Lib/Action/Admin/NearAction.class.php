<?php
class NearAction extends CommonAction
{
    public function index()
    {
        $this->display();
    }
    //定位
    public function dingwei()
    {
        $lat = cookie('lat');
        $lng = cookie('lng');
        if (cookie('localed') != 2 || empty($lat) || empty($lng)) {
            $local = D('Near')->GetLocation($this->_param('lat'), $this->_param('lng'));
            cookie('lat_ok', $local['lat'], 3600);
            cookie('lng_ok', $local['lng'], 3600);
            cookie('lat', $local['lat'], 3600);
            cookie('lng', $local['lng'], 3600);
            $addr = D('Near')->GetAddress($local['lat'], $local['lng']);
            cookie('addr', $addr, 3600);
            if (!empty($addr)) {
                cookie('localed', 2);
            }
        }
        echo "1";
    }
    //地址
    public function address()
    {
        $addr = cookie('addr');
        echo $addr;
    }
    public function reset()
    {
        $local = D('Near')->GetLocation($this->_param('lat'), $this->_param('lng'));
        cookie('lat_ok', $local['lat'], 3600);
        cookie('lng_ok', $local['lng'], 3600);
        cookie('lat', $local['lat'], 3600);
        cookie('lng', $local['lng'], 3600);
        $addr = D('Near')->GetAddress($local['lat'], $local['lng']);
        cookie('addr', $addr, 3600);
        if (!empty($addr)) {
            cookie('localed', 1);
        }
        echo $addr;
    }
    //搜索
    public function search()
    {
        $keyword = urlencode($this->_param('keyword', 'htmlspecialchars'));
        $this->assign('nextpage', LinkTo('near/loaddata', array('keyword' => $keyword, 't' => NOW_TIME, 'p' => '0000')));
        $this->assign('keyword', urldecode($keyword));
        $this->display();
    }
    public function loaddata()
    {
        set_time_limit(0);
        $lat = cookie('lat');
        $lng = cookie('lng');
        if (empty($lat) || empty($lng)) {
            die;
        }
        $keyword = $this->_param('keyword');
        import('ORG.Util.Page');
        // 导入分页类
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        //验证城市信息
        if (session('city_code') == '') {
            $place = D('Cloud')->GetAddress($lat, $lng);
            $place = json_decode($place);
            $city_code = $place->city_code;
            session('city_code', $city_code);
        } else {
            $city_code = session('city_code');
        }
        // 获取周边信息
        $key = urldecode($keyword);
        $arr = D('Cloud')->NearData($lat, $lng, $key, $p);
        $num = intval($arr->total);
        $result = $arr->results;
        if ($num > 0) {
            foreach ($result as $value) {
                $data = array();
                $data['uid'] = $value->uid;
                $data['name'] = $value->name;
                $data['type'] = $value->detail_info->type;
                $data['lat'] = $value->location->lat;
                $data['lng'] = $value->location->lng;
                $data['telephone'] = $value->telephone;
                $data['address'] = $value->address;
                $data['tag'] = $value->detail_info->tag;
                $data['orderby'] = 0;
                $data['create_time'] = NOW_TIME;
                if ($this->_CONFIG['site']['citycode'] == $city_code) {
                    // 查询数据库是否有记录,有:大于30天更新，没有则录入数据
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
                $poi[] = $data;
            }

        }
        $this->assign('lat', $lat);
        $this->assign('lng', $lng);
        $this->assign('keyword', urldecode($keyword));
        $this->assign('poi', $poi);
        $this->display();
    }
    public function detail($id)
    {
        //获取用户自定坐标
        $local = D('Near')->GetLocation();
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
    public function gps($id)
    {
        //获取用户自定坐标
        $local = D('Near')->GetLocation();
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
    public function select()
    {
        //获取用户自定坐标
        $lat = cookie('lat_ok');
        $lng = cookie('lng_ok');
        if (empty($lat) || empty($lng)) {
            $lat = cookie('lat');
            $lng = cookie('lng');
        }
        //获取用户数据库坐标
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
    public function load()
    {
        $lat = $this->_param('lat', 'htmlspecialchars');
        $lng = $this->_param('lng', 'htmlspecialchars');
        //获取地址推荐
        $url = 'http://api.map.baidu.com/geocoder/v2/?ak=C9613fa45f450daa331d85184c920119&location=' . $lat . ',' . $lng . '&output=json&pois=1';
        $json = file_get_contents($url);
        $geo = object_array(json_decode($json));
        $this->assign('geo', $geo);
        $this->display();
        // 输出模板
    }
    public function selected()
    {
        $lat = $this->_param('lat', 'htmlspecialchars');
        $lng = $this->_param('lng', 'htmlspecialchars');
        cookie('lat_ok', null);
        cookie('lng_ok', null);
        cookie('lat_ok', $lat);
        cookie('lng_ok', $lng);
        $this->fengmiAlert('您的位置已经重置，请返回继续浏览！', U('index/index'));
    }
}