<?php
class WeatherAction extends CommonAction {

	//天气主页
    public function index() {
		
		$date = date('Y-m-d',time());
		$weather = M('weather_content')->where(array('date' => $date))->find();
		$report = M("weather_forecast")->order('id')->limit('7')->select(); 
		$this->assign('report', $report);
		$this->assign('today', $weather);
        $this->display();
    }


	
    public function update() {
			//授权验证
			D('Activity')->updateCount(1, 'cate_id', 100);
			$key =  $this->_param('key'); 
			
			if($key == '2db6db61244c5cc3d13456696af'){
				
			
				
		
			// 天气API获取
			$url = 'http://op.juhe.cn/onebox/weather/query?key='.$this->_CONFIG['site']['weatherkey'].'&dtype=json&cityname='.urlencode($this->_CONFIG['site']['cityname']);
			
			
			// 获取百度API数据
			$data = file_get_contents($url);
			$data =json_decode($data,true);
			$result = $data['result']['data'];

			$timeline = time();
			//当天天气数据综合
			$real = $result['realtime'];
			$life = $result['life'];
			//未来天气数据综合
			$last = $result['weather'];
			//未来天气数据综合
			$pm25 = $result['pm25'];
			//细化当天数据
			$array = array();
			$date = $real['date'];
			$array['date'] = $real['date'];
			$array['week'] = $real['week'];
			$array['moon'] = $real['moon'];
			$array['weather'] = $real['weather']['info'];
			$array['temperature'] = $real['weather']['temperature'];
			$array['humidity'] = $real['weather']['humidity'];
			$array['img'] = $real['weather']['img'];
			$array['direct'] = $real['wind']['direct'];
			$array['power'] = $real['wind']['power'];
			$array['speed'] = $real['wind']['windspeed']!=''? $real['wind']['windspeed'] :'';
			$array['pm25'] = $pm25['pm25']['pm25'];
			$array['pm10'] = $pm25['pm25']['pm10'];
			$array['pmlevel'] = $pm25['pm25']['level'];
			$array['chuanyi'] = $life['info']['chuanyi'][0].'|||'.$life['info']['chuanyi'][1];
			$array['ganmao'] = $life['info']['ganmao'][0].'|||'.$life['info']['ganmao'][1];
			$array['kongtiao'] = $life['info']['kongtiao'][0].'|||'.$life['info']['kongtiao'][1];
			$array['wuran'] = $life['info']['wuran'][0].'|||'.$life['info']['wuran'][1];
			$array['xiche'] = $life['info']['xiche'][0].'|||'.$life['info']['xiche'][1];
			$array['yundong'] = $life['info']['yundong'][0].'|||'.$life['info']['yundong'][1];
			$array['ziwaixian'] = $life['info']['ziwaixian'][0].'|||'.$life['info']['ziwaixian'][1];
			$array['pmdes'] = $pm25['pm25']['quality'].'|||'.$pm25['pm25']['des'];
			$array['timeline'] = $timeline;
			
			// 写入今日数据
			if($date!=''){
				//查询数据库中数据
				$detail = M('weather_content')->where(array('date' => $date))->count();
				$obj = M('weather_content');
				if($detail == 0){
					$obj->add($array);
				}else{
					$result= $obj->where(array('date' => $date))->data($array)->save();
				}
			}
			
			//写出今日数据缓存
			
			
			
			
			//未来天气预报
			for($x=0; $x<=6; $x++){
				$info = $last[$x];
				$dawn = $last[$x]['info']['dawn'];
				$day = $last[$x]['info']['day'];
				$night = $last[$x]['info']['night'];
				$isdawn = count($dawn);
				$date = $info['date'];
				$detail = $day[1];
			   
				$array = array();
				$array['date'] = $info['date'];
				$array['week'] = $info['week'];
				$array['moon'] = $info['nongli'];
				
				if( $isdawn > 2 ){
					$array['dawn_weather'] = $dawn[1];
					$array['dawn_temperature'] = $dawn[2];
					$array['dawn_wind'] = $dawn[3];
					$array['dawn_power'] = $dawn[4];
					$array['dawn_id'] = $dawn[0];
				}
				
				$array['day_weather'] = $day[1];
				$array['day_temperature'] = $day[2];
				$array['day_wind'] = $day[3];
				$array['day_power'] = $day[4];
				$array['day_id'] = $day[0];
				
				$array['night_weather'] = $night[1];
				$array['night_temperature'] = $night[2];
				$array['night_wind'] = $night[3];
				$array['night_power'] = $night[4];
				$array['night_id'] = $night[0];
				$array['timeline'] = $timeline;
				
				
				if($date!=''){
					
					$detail = M('weather_forecast')->where(array('date' => $date))->count();
					$obj = M('weather_forecast');
					if($detail == 0 ){
						$obj->add($array);
					}else{
						$result= $obj->where(array('date' => $date))->data($array)->save();
					}
				
				}
			}
			
			echo 'NIUCMS VERY NIUBI?';
			
		}else{
			echo 'NIUCMS VERY NIUBI!';
		}
	
	}
	
	
}