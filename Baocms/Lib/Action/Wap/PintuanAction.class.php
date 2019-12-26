<?php

class PintuanAction extends CommonAction {

	public function _initialize() {
		parent::_initialize();
		if ($this->_CONFIG['operation']['pintuan'] == 0) {
            $this->error('此功能已关闭');
            die;
        }
		$appid = $this -> _CONFIG['weixin']["appid"];
		$appsecret = $this -> _CONFIG['weixin']["appsecret"];
		import("@/Net.Jssdk");
		$jssdk = new JSSDK("$appid", "$appsecret");
		$signPackage = $jssdk -> GetSignPackage();
		$this -> signPackage = $signPackage;
	}

	public function index() {
		$cate = (int)$this -> _get('id');
		$cates = D('Pcate') -> find($cate);
		$this -> assign('cates', $cates);
		$autocates = D('Pcate') -> order(array('csort' => 'asc')) -> select();
		$this -> assign('autocates', $autocates);
		
		$keyword = $this -> _param('keyword', 'htmlspecialchars');
		$this -> assign('keyword', $keyword);
		
		$order = $this -> _param('order', 'htmlspecialchars');
		$this -> assign('order', $order);
		
		$price = $this -> _param('price', 'htmlspecialchars');
		$this -> assign('price', $price);
		$this -> assign('nextpage', LinkTo('pintuan/loaddata', array('t' => NOW_TIME, 'id' => $cate,'price' => $price,'order' => $order,'keyword' => $keyword, 'p' => '0000')));
		$this -> display();
		// 输出模板
	}

	public function cates() {
		$cate = (int)$this -> _get('id');
		$cates = D('Pcate') -> find($cate);
		$this -> assign('cates', $cates);
		$autocates = D('Pcate') -> order(array('csort' => 'asc')) -> select();
		$this -> assign('autocates', $autocates);
		
		$keyword = $this -> _param('keyword', 'htmlspecialchars');
		$this -> assign('keyword', $keyword);
		
		$price = $this -> _param('price', 'htmlspecialchars');
		$this -> assign('price', $price);
		
		$order = $this -> _param('order', 'htmlspecialchars');
		$this -> assign('order', $order);
		
		$this -> assign('nextpage', LinkTo('pintuan/loaddata', array('t' => NOW_TIME, 'id' => $cate, 'price' => $price,'order' => $order,'keyword' => $keyword, 'p' => '0000')));
		$this -> display();
		// 输出模板
	}

	//有问题需要协助解决下
	public function loaddata() {
		$pintuan = D('Pgoods');
		import('ORG.Util.Page');
		// 导入分页类 
		//初始数据
		$map = array('is_show' => 1);
		$cate = (int)$this -> _param('id');
		if ($cate) {
			$map['cate_id'] = $cate;
		}
		if ($keyword = $this -> _param('keyword', 'htmlspecialchars')) {
			$map['name'] = array('LIKE', '%' . $keyword . '%');
		}
		
		$price = (int) $this->_param('price');
	
        switch ($price) {
            case 1:
                $map['tuan_price'] = array('ELT', '5000');
                break;
            case 2:
                $map['tuan_price'] = array('between', '5001,10000');
                break;
            case 3:
                $map['tuan_price'] = array('between', '10001,20000');
                break;
            case 4:
                $map['tuan_price'] = array('EGT', '20001');
                break;
        }
        $this->assign('price', $price);
	
		$orderby = '';
        switch ($order) {
            case 3:
                $orderby = array('market_price' => 'desc');//市场价格
                break;
            case 2:
				$orderby = array('virtual_sales_num' => 'desc');//销售数量
                break;
            default:
                $orderby = array('add_time' => 'desc');//发布时间
                break;
        }
		
		
		$count = $pintuan -> where($map) -> count();
		// 查询满足要求的总记录数
		$Page = new Page($count, 8);
		// 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page -> show();
		// 分页显示输出

		$var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
		$p = $_GET[$var];
		if ($Page -> totalPages < $p) {
			die('0');
		}
		$list = $pintuan -> order($orderby) -> where($map) -> limit($Page -> firstRow . ',' . $Page -> listRows) -> select();
		$this -> assign('list', $list);
		// 赋值数据集
		$this -> assign('page', $show);
		// 赋值分页输出
		$this -> display();
		// 输出模板
	}

	public function detail($id) {
		$goods_id = (int)$id;
		$detail = D('Pgoods') -> find($goods_id);
		$sale_num = $detail['sales_num'] + $detail['virtual_sales_num'];
		$laTuanNum = $detail['tuan_num'] - 1;
		$shop_id = $detail['shop_id'];
		$dianid = D('Pshop') -> where(array('id' => $shop_id)) -> find();
		$daoTimes = time() - ($detail['tuan_hours'] * 3600);
		$map['goods_id'] = $goods_id;
		$map['tuan_status'] = 2;
		$map['tstatus'] = array('IN', array(1, 2));
		$map['order_time'] = array('elt', $daoTimes);
		$pguoqiss = D('Porder') -> where($map) -> select();
		$pguoqi = count($pguoqiss);
		if ($pguoqi != 0) {
			$pguoqidata = array('tuan_status' => '4', 'order_status' => '7');
			D('Porder') -> where($map) -> setField($pguoqidata);
			D('Ptuan') -> where(array('goods_id' => $goods_id, 'tuan_status' => 2, 'tuan_time' => array('elt', $daoTimes))) -> setField('tuan_status', '4');
			D('Ptuanteam') -> where(array('goods_id' => $goods_id, 'tuan_status' => 2, 'add_time' => array('elt', $daoTimes))) -> setField('tuan_status', '4');
			foreach ($pguoqiss as $key => $val) {
				$userid = $val['user_id'];
				//====================拼团成功通知===========================
				include_once "Baocms/Lib/Net/Wxmesg.class.php";
				$_data_order = array(//整体变更
					'url' => "http://" . $_SERVER['HTTP_HOST'] . "/user/pintuan/order/id/" . $val['id'] . ".html", 
					'topcolor' => '#F55555', 'first' => '很抱歉，您参与的拼团，因在有效期限内没有成功拼团~', 
					'payprice' => round($val['pay_price'] / 100, 2) . '元', 
					'goodsName' => $val['goods_name'], 
					'orderno' => $val['order_no'], 
					'remark' => '点击查看订单详情', 
				);
				$order_data = Wxmesg::ctover($_data_order);
				$return = Wxmesg::net($userid, 'OPENTM401202557', $order_data);
				//结束
				//====================拼团成功通知===============================
			}
		}
		$tuanListTmp = D('Ptuan') -> order("tuan_time DESC") -> where(array('goods_id' => $goods_id, 'tuan_status' => 2)) -> limit(0, 10) -> select();
		$user_ids = $tuanList = array();
		if (is_array($tuanListTmp) && !empty($tuanListTmp)) {
			foreach ($tuanListTmp as $key => $value) {
				$tuanList[$key] = $value;
				$user_ids[$value['user_id']] = $value['user_id'];
				$goodsInfoTmp = D('Pgoods') -> find($value['goods_id']);
				if ($value['tlevel'] == 1) {
					$goodsInfoTmp['tuan_num'] = $goodsInfoTmp['tuan_num'];
					$goodsInfoTmp['tuan_price'] = $goodsInfoTmp['tuan_price'];
				} else if ($value['tlevel'] == 2) {
					$goodsInfoTmp['tuan_num'] = $goodsInfoTmp['tuan_num2'];
					$goodsInfoTmp['tuan_price'] = $goodsInfoTmp['tuan_price2'];
				} else if ($value['tlevel'] == 3) {
					$goodsInfoTmp['tuan_num'] = $goodsInfoTmp['tuan_num3'];
					$goodsInfoTmp['tuan_price'] = $goodsInfoTmp['tuan_price3'];
				}
				$tuanList[$key]['tuan_price'] = round($goodsInfoTmp['tuan_price'] / 100, 2);
				$tuanList[$key]['tuan_time'] = date('Y-m-d H:i:s', $value['tuan_time']);
				$tuanList[$key]['tuanUrl'] = "/wap/pintuan/tuan/id/" . $value['id'];

				$tuanTeamListTmp = D('ptuanteam') -> where(array('tuan_id' => $value['id'], 'tuan_status' => 2)) -> count();

				$tuanList[$key]['shengyuTuanTeamNum'] = $goodsInfoTmp['tuan_num'] - $tuanTeamListTmp;
				
				

			}
		}
		//p($tuanList);die;
		$this -> assign('dianid', $dianid);
		$this -> assign('sale_num', $sale_num);
		$this -> assign('laTuanNum', $laTuanNum);
		$this -> assign('detail', $detail);
		$this -> assign('users', D('Users') -> itemsByIds($user_ids));
		$this -> assign('tuanTeamListCount', $tuanTeamListCount);
		$this -> assign('tuanList', $tuanList);
		$this -> display();
	}

	public function buy() {
		if (empty($this -> uid)) {
			header("Location:" . U('passport/login'));
			die ;
		}
		$tstatus = (int)$this -> _get('tstatus');
		$tlevel = (int)$this -> _get('tlevel');
		$goods_id = (int)$this -> _get('id');
		$order_id = (int)$this -> _get('order_id');
		$tuan_id = isset($_GET['tuan_id']) ? intval($_GET['tuan_id']) : 0;
		$address_id = isset($_GET['address_id']) ? intval($_GET['address_id']) : 0;
		$addobj = D('Paddress');
		$addressCount = $addobj -> where(array('user_id' => $this -> uid)) -> count();
		if ($addressCount == 0) {
			header("Location:" . U('pintuan/addrcat', array('tstatus' => $tstatus, 'tuan_id' => $tuan_id, 'tlevel' => $tlevel, 'id' => $goods_id, 'order_id' => $order_id)));
		} else {
			$defaultCount = $addobj -> where(array('user_id' => $this -> uid, 'default' => 1)) -> count();
			if ($defaultCount == 0) {
				$defaultAddress = $addobj -> where(array('user_id' => $this -> uid)) -> order("id desc") -> find();
			} else {
				if ($address_id == 0) {
					$defaultAddress = $addobj -> where(array('user_id' => $this -> uid, 'default' => 1)) -> find();
				} else {
					$defaultAddress = $addobj -> where(array('user_id' => $this -> uid, 'id' => $address_id)) -> find();
				}
			}
		}
		$changeAddressUrl = "http://" . $_SERVER['HTTP_HOST'] . U('pintuan/addlist', array('tstatus' => $tstatus, 'tuan_id' => $tuan_id, 'tlevel' => $tlevel, 'id' => $goods_id, 'order_id' => $order_id));
		$detail = D('Pgoods') -> find($goods_id);
		if ($tstatus == 2) {
			if ($tlevel == 1) {
				$tuan_price = $detail['tuan_price'];
			} elseif ($tlevel == 2) {
				$tuan_price = $detail['tuan_price2'];
			} elseif ($tlevel == 3) {
				$tuan_price = $detail['tuan_price3'];
			}
		} elseif ($tstatus == 1) {
			$tuan_price = $detail['tuanz_price'];
		} elseif ($tstatus == 0) {
			$tuan_price = $detail['one_price'];
		}
		$shop = D('Pshop') -> find($detail['shop']);
		$shenid = D('Pshop') -> where(array('tongchen' => array('IN', $defaultAddress['province_id']))) -> find();
		$shiid = D('Pshop') -> where(array('tongchen' => array('IN', $defaultAddress['city_id']))) -> find();
		$xianid = D('Pshop') -> where(array('tongchen' => array('IN', $defaultAddress['area_id']))) -> find();
		$mianshen = D('Pgoods') -> where(array('yunfei_ids' => array('IN', $defaultAddress['province_id']), 'id' => $goods_id)) -> find();
		$mianshi = D('Pgoods') -> where(array('yunfei_ids' => array('IN', $defaultAddress['province_id']), 'id' => $goods_id)) -> find();
		$mianxian = D('Pgoods') -> where(array('yunfei_ids' => array('IN', $defaultAddress['province_id']), 'id' => $goods_id)) -> find();
		if (!empty($shenid) || !empty($shiid) || !empty($xianid)) {
			$express = D('Pkuaidi') -> where(array('id' => 1)) -> getField('name');
			$express_price = 0;
		} else {
			if ($detail['is_yunfei'] == 1 || $shop['mianyunfei'] == 1 || !empty($mianshen) || !empty($mianshi) || !empty($mianxian)) {
				$express = D('Pkuaidi') -> where(array('id' => $detail['kuaidi'])) -> getField('name');
				$express_price = 0;
			} else {
				$express = D('Pkuaidi') -> where(array('id' => $detail['kuaidi'])) -> getField('name');
				$qyjg = D('Pyunfei') -> where(array('kuaidi_id' => $detail['kuaidi'], 'province_id' => $defaultAddress['province_id'])) -> find();
				$express_price = $qyjg['shouzhong'] + (($detail['zhongliang'] - 1) * $qyjg['xuzhong']);
			}
		}
		$this -> assign('tlevel', $tlevel);
		$this -> assign('tuan_id', $tuan_id);
		$this -> assign('users', $this -> uid);
		$this -> assign('addressCount', $addressCount);
		$this -> assign('defaultAddress', $defaultAddress);
		$this -> assign('detail', $detail);
		$this -> assign('price', $tuan_price);
		$this -> assign('tstatus', $tstatus);
		$this -> assign('changeAddressUrl', $changeAddressUrl);
		$this -> assign('express', $express);
		$this -> assign('express_price', $express_price);
		$this -> assign('order_id', $order_id);
		$this -> display();
		die ;
	}

	public function addlist() {
		$tstatus = (int)$this -> _get('tstatus');
		$tlevel = (int)$this -> _get('tlevel');
		$goods_id = (int)$this -> _get('id');
		$tuan_id = isset($_GET['tuan_id']) ? intval($_GET['tuan_id']) : 0;
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$addrcat = "http://" . $_SERVER['HTTP_HOST'] . U('pintuan/addrcat', array('tstatus' => $tstatus, 'tuan_id' => $tuan_id, 'tlevel' => $tlevel, 'id' => $goods_id, 'order_id' => $order_id));
		$ud = D('Paddress');
		$defaultadd = $ud -> where(array('user_id' => $this -> uid, 'default' => 1)) -> select();
		$addlist = $ud -> where(array('user_id' => $this -> uid, 'default' => 0)) -> order("id desc") -> select();
		if ($tuan_id == 0) {
			$buyUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/pintuan/buy/tstatus/" . $tstatus . "/tlevel/" . $tlevel . "/id/" . $goods_id . "/order_id/" . $order_id . "/address_id/";
			$editUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/pintuan/addedit/tstatus/" . $tstatus . "/tlevel/" . $tlevel . "/id/" . $goods_id . "/order_id/" . $order_id . "/address_id/";
			$delUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/pintuan/adddels/tstatus/" . $tstatus . "/tlevel/" . $tlevel . "/id/" . $goods_id . "/order_id/" . $order_id . "/address_id/";
		} else {
			$buyUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/pintuan/buy/tstatus/" . $tstatus . "/tuan_id/" . $tuan_id . "/tlevel/" . $tlevel . "/id/" . $goods_id . "/order_id/" . $order_id . "/address_id/";
			$editUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/pintuan/addedit/tstatus/" . $tstatus . "/tuan_id/" . $tuan_id . "/tlevel/" . $tlevel . "/id/" . $goods_id . "/order_id/" . $order_id . "/address_id/";
			$delUrl = "http://" . $_SERVER['HTTP_HOST'] . "/wap/pintuan/adddels/tstatus/" . $tstatus . "/tuan_id/" . $tuan_id . "/tlevel/" . $tlevel . "/id/" . $goods_id . "/order_id/" . $order_id . "/address_id/";
		}
		$this -> assign('addrcat', $addrcat);
		$this -> assign('defaultadd', $defaultadd);
		$this -> assign('addlist', $addlist);
		$this -> assign('buyUrl', $buyUrl);
		$this -> assign('editUrl', $editUrl);
		$this -> assign('delUrl', $delUrl);
		$this -> display();
	}

	public function addrcat() {
		$tstatus = (int)$this -> _get('tstatus');
		$tlevel = (int)$this -> _get('tlevel');
		$goods_id = (int)$this -> _get('id');
		$tuan_id = isset($_GET['tuan_id']) ? intval($_GET['tuan_id']) : 0;
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$provinceList = D('Paddlist') -> where(array('level' => 1)) -> select();
		$buyUrl = "http://" . $_SERVER['HTTP_HOST'] . U('pintuan/buy', array('tstatus' => $tstatus, 'tuan_id' => $tuan_id, 'tlevel' => $tlevel, 'id' => $goods_id, 'order_id' => $order_id));
		$this -> assign('provinceList', $provinceList);
		$this -> assign('buyUrl', $buyUrl);
		$this -> display();
	}

	public function addedit() {
		$tstatus = (int)$this -> _get('tstatus');
		$tlevel = (int)$this -> _get('tlevel');
		$goods_id = (int)$this -> _get('id');
		$address_id = (int)$this -> _get('address_id');
		$tuan_id = isset($_GET['tuan_id']) ? intval($_GET['tuan_id']) : 0;
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$provinceList = D('Paddlist') -> where(array('level' => 1)) -> select();
		$detail = D('Paddress') -> where(array('user_id' => $this -> uid, 'id' => $address_id)) -> find();
		$buyUrl = "http://" . $_SERVER['HTTP_HOST'] . U('pintuan/buy', array('tstatus' => $tstatus, 'tuan_id' => $tuan_id, 'tlevel' => $tlevel, 'id' => $goods_id, 'order_id' => $order_id, 'address_id' => $address_id));

		$cityList = D('Paddlist') -> where(array('upid' => $detail['province_id'])) -> select();
		$areaList = D('Paddlist') -> where(array('upid' => $detail['city_id'])) -> select();
		$this -> assign('provinceList', $provinceList);
		$this -> assign('cityList', $cityList);
		$this -> assign('areaList', $areaList);
		$this -> assign('detail', $detail);
		$this -> assign('buyUrl', $buyUrl);
		$this -> display();
	}

	public function adddels() {
		$tstatus = (int)$this -> _get('tstatus');
		$tlevel = (int)$this -> _get('tlevel');
		$goods_id = (int)$this -> _get('id');
		$address_id = (int)$this -> _get('address_id');
		$tuan_id = isset($_GET['tuan_id']) ? intval($_GET['tuan_id']) : 0;
		$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
		$obj = D('Paddress');
		if ($obj -> delete($address_id)) {
			$this -> success('删除成功！', U('pintuan/addlist', array('tstatus' => $tstatus, 'tuan_id' => $tuan_id, 'tlevel' => $tlevel, 'id' => $goods_id, 'order_id' => $order_id)));

		}
		$this -> display();
	}

	public function addsave() {
		$defaults = I('defaults', '', 'intval,trim');
		$xm = I('addxm', '', 'trim,htmlspecialchars');
		$tel = I('addtel', '', 'trim');
		$province = I('province', '', 'intval,trim');
		$city = I('city', '', 'intval,trim');
		$area = I('areas', '', 'intval,trim');
		$info = I('addinfo', '', 'trim,htmlspecialchars');
		$provinfo = D('Paddlist') -> find($province);
		$cityinfo = D('Paddlist') -> find($city);
		$areainfo = D('Paddlist') -> find($area);
		$newadd = array('user_id' => $this -> uid, 'default' => $defaults, 'xm' => $xm, 'tel' => $tel, 'province_id' => $province, 'city_id' => $city, 'area_id' => $area, 'area_str' => $provinfo['name'] . " " . $cityinfo['name'] . " " . $areainfo['name'], 'info' => $info, );
		$ud = D('Paddress');
		if ($defaults == 1) {
			$up1 = $ud -> where('user_id =' . $this -> uid) -> setField('default', 0);
		}
		if ($ud -> add($newadd)) {
			$outArr = array('status' => 200);
			echo json_encode($outArr);
			exit ;
		} else {
			$outArr = array('status' => 11111);
			echo json_encode($outArr);
			exit ;
		}
	}

	public function addsave2() {
		$addid = I('addid', '', 'intval,trim');
		$defaults = I('defaults', '', 'intval,trim');
		$xm = I('addxm', '', 'trim,htmlspecialchars');
		$tel = I('addtel', '', 'trim');
		$province = I('province', '', 'intval,trim');
		$city = I('city', '', 'intval,trim');
		$area = I('areas', '', 'intval,trim');
		$info = I('addinfo', '', 'trim,htmlspecialchars');
		$provinfo = D('Paddlist') -> find($province);
		$cityinfo = D('Paddlist') -> find($city);
		$areainfo = D('Paddlist') -> find($area);
		$newadd = array('id' => $addid, 'user_id' => $this -> uid, 'default' => $defaults, 'xm' => $xm, 'tel' => $tel, 'province_id' => $province, 'city_id' => $city, 'area_id' => $area, 'area_str' => $provinfo['name'] . " " . $cityinfo['name'] . " " . $areainfo['name'], 'info' => $info, );
		$ud = D('Paddress');
		if ($defaults == 1) {
			$up1 = $ud -> where('user_id =' . $this -> uid) -> setField('default', 0);
		}
		if ($ud -> save($newadd)) {
			$outArr = array('status' => 200);
			echo json_encode($outArr);
			exit ;
		} else {
			$outArr = array('status' => 11111);
			echo json_encode($outArr);
			exit ;
		}
	}

	public function city() {
		$upid = isset($_GET['upid']) ? intval($_GET['upid']) : 0;
		$callback = $_GET['callback'];
		$outArr = array();
		$cityList = D('Paddlist') -> where(array('upid' => $upid)) -> select();
		if (is_array($cityList) && !empty($cityList)) {
			foreach ($cityList as $key => $value) {
				$outArr[$key]['id'] = $value['id'];
				$outArr[$key]['name'] = $value['name'];
			}
		}
		$outStr = '';
		$outStr = json_encode($outArr);
		if ($callback) {
			$outStr = $callback . "(" . $outStr . ")";
		}
		echo $outStr;
		die();
	}

	public function pay() {
		$goods_id = I('goods_id', '', 'intval,trim');
		$code = I('code', '', 'trim,htmlspecialchars');
		$address_id = I('address_id', '', 'intval,trim');
		$tstatus = I('tstatus', '', 'intval,trim');
		$tlevel = I('tlevel', '', 'intval,trim');
		$tijiao_id = I('order_id', '', 'intval,trim');
		$tuan_id = I('tuan_id', '', 'intval,trim');
		$goods_price = I('goods_price', '', 'trim');
		$express_name = I('express_name', '', 'trim,htmlspecialchars');
		$express_price = I('express_price', '', 'trim');
		$order_beizu = I('order_beizu', '', 'trim,htmlspecialchars');
		$goods_num = I('goods_num', '', 'intval,trim');
		$pay_price = $express_price + $goods_price;
		$address = D('Paddress') -> find($address_id);
		$pgoods = D('Pgoods') -> find($goods_id);
		if ($pgoods['xiangou_num'] <= 0) {
			//$this -> ajaxReturn(array('status' => 304));
			$outArr = array('status' => 304, 'tuan_id' => $tuan_id, );
			echo json_encode($outArr);
			exit ;
			exit ;
		}
		if ($tlevel == 1) {
			$renshu = $pgoods['tuan_num'];
		} elseif ($tlevel == 2) {
			$renshu = $pgoods['tuan_num2'];
		} elseif ($tlevel == 3) {
			$renshu = $pgoods['tuan_num3'];
		}
		if ($tijiao_id != 0 || $tstatus == 2) {
			$tuanTeamListCount = D('Ptuanteam') -> where(array('tuan_status' => 2, 'tuan_id' => $tuan_id)) -> count();
		}
		$dataorder = array('tstatus' => $tstatus, 'goods_id' => $goods_id, 'goods_name' => $pgoods['name'], 'goods_num' => $goods_num, 'goods_price' => $goods_price, 'pay_price' => $pay_price, 'user_id' => $this -> uid, 'xm' => $address['xm'], 'tel' => $address['tel'], 'address' => $address['area_str'] . " " . $address['info'], 'express_name' => $express_name, 'express_price' => $express_price, 'order_time' => time(), 'order_beizu' => $order_beizu, 'renshu' => $renshu, 'address_id' => $address_id);
		$datatuan = array('goods_id' => $goods_id, 'user_id' => $this -> uid, 'tlevel' => $tlevel, 'tuan_time' => time(), );
		if ($tijiao_id != 0) {
			if ($renshu <= $tuanTeamListCount) {
				$outArr = array('status' => 305, 'tuan_id' => $tuan_id, );
				echo json_encode($outArr);
				exit ;
			} else {
				D('Porder') -> save(array('id' => $tijiao_id, 'pay_price' => $pay_price, 'xm' => $address['xm'], 'tel' => $address['tel'], 'address' => $address['area_str'] . " " . $address['info'], 'express_name' => $express_name, 'express_price' => $express_price, 'order_beizu' => $order_beizu, 'address_id' => $address_id));
				$logs = array('type' => 'pintuan', 'user_id' => $this -> uid, 'order_id' => $order_id, 'code' => $code, 'need_pay' => $pay_price, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'is_paid' => 0);
				$logs['log_id'] = D('Paymentlogs') -> add($logs);
				$log_id = $logs['log_id'];
				$outArr = array('status' => 200, 'log_id' => $log_id, );
				echo json_encode($outArr);
				exit ;
			}
		} else {
			if ($tstatus == 0) {
				$dataorder['tuan_id'] = 0;
				$order['id'] = D('Porder') -> add($dataorder);
				$order_id = $order['id'];
				$logs = array('type' => 'pintuan', 'user_id' => $this -> uid, 'order_id' => $order_id, 'code' => $code, 'need_pay' => $pay_price, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'is_paid' => 0);
				$logs['log_id'] = D('Paymentlogs') -> add($logs);
				$log_id = $logs['log_id'];
				//$this -> ajaxReturn(array('status' => 200, 'tuanurl' => $tuanurl));
				$outArr = array('status' => 200, 'log_id' => $log_id, );
				echo json_encode($outArr);
				exit ;
				// 普通订单下单
			} elseif ($tstatus == 1 && $tuan_id == 0) {
				$ptuan['id'] = D('Ptuan') -> add($datatuan);
				$dataorder['tuan_id'] = $ptuan['id'];
				$order['id'] = D('Porder') -> add($dataorder);
				$datateam = array('tuan_id' => $ptuan['id'], 'goods_id' => $goods_id, 'user_id' => $this -> uid, 'order_id' => $order['id'], 'type_id' => 1, 'add_time' => time(), );
				D('ptuanteam') -> add($datateam);
				$order_id = $order['id'];
				$logs = array('type' => 'pintuan', 'user_id' => $this -> uid, 'order_id' => $order_id, 'code' => $code, 'need_pay' => $pay_price, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'is_paid' => 0);
				$logs['log_id'] = D('Paymentlogs') -> add($logs);
				$log_id = $logs['log_id'];
				//$this -> ajaxReturn(array('status' => 200, 'tuanurl' => $tuanurl));
				$outArr = array('status' => 200, 'log_id' => $log_id, );
				echo json_encode($outArr);
				exit ;

				// 开团订单下单
			} elseif ($tstatus == 2) {
				if ($renshu <= $tuanTeamListCount) {
					$outArr = array('status' => 305, 'tuan_id' => $tuan_id, );
					echo json_encode($outArr);
					exit ;
				} else {
					$dataorder['tuan_id'] = $tuan_id;
					$order['id'] = D('Porder') -> add($dataorder);
					$datateam = array('tuan_id' => $tuan_id, 'goods_id' => $goods_id, 'user_id' => $this -> uid, 'order_id' => $order['id'], 'type_id' => 2, 'add_time' => time(), );
					D('ptuanteam') -> add($datateam);
					$order_id = $order['id'];
					$logs = array('type' => 'pintuan', 'user_id' => $this -> uid, 'order_id' => $order_id, 'code' => $code, 'need_pay' => $pay_price, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'is_paid' => 0);
					$logs['log_id'] = D('Paymentlogs') -> add($logs);
					$log_id = $logs['log_id'];
					//$this -> ajaxReturn(array('status' => 200, 'tuanurl' => $tuanurl));
					$outArr = array('status' => 200, 'log_id' => $log_id, );
					echo json_encode($outArr);
					exit ;
					//参团订单
				}
			}
		}
	}

	public function tuan() {
		$tuan_id = (int)$this -> _get('id');
		$tuanInfo = D('Ptuan') -> find($tuan_id);
		$goods = D('Pgoods') -> find($tuanInfo['goods_id']);
		if ($tuanInfo['tlevel'] == 1) {
			$tuan_num = $goods['tuan_num'];
			$tuan_price = $goods['tuan_price'];
		} elseif ($tuanInfo['tlevel'] == 2) {
			$tuan_num = $goods['tuan_num2'];
			$tuan_price = $goods['tuan_price2'];
		} elseif ($tuanInfo['tlevel'] == 3) {
			$tuan_num = $goods['tuan_num3'];
			$tuan_price = $goods['tuan_price3'];
		}
		$tusers = D('Users') -> find($tuanInfo['user_id']);
		//$list=M('Ptuanteam')->join('Porder ON Ptuanteam.tuan_id=Porder.tuan_id')->field('user_id,order_status,tuan_id,type_id')-> where(array('tuan_id' => $tuan_id, 'type_id' => 2,'order_status' => 2)->select();
		$lists = D('Ptuanteam') -> where(array('tuan_id' => $tuan_id, 'type_id' => 2, 'tuan_status' => 2)) -> select();
		$ids = array();
		foreach ($lists as $k => $val) {
			$ids[$val['user_id']] = $val['user_id'];
		}
		$canyu = count($lists);
		$Porder = D('Porder');
		$dulu = $Porder -> where(array('user_id' => $tuanInfo['user_id'], 'tuan_id' => $tuan_id)) -> find();
		if (empty($dulu['order_no'])) {
			$shengyu = $tuan_num;
		} else {
			$shengyu = $tuan_num - $canyu - 1;
		}
		if ($shengyu <= 0 && $tuanInfo['tuan_status'] == 2) {
			$updata = array('tuan_status' => 3, 'order_status' => 3);
			$Porder -> where(array('tuan_id' => $tuan_id, 'order_status' => 2)) -> setField($updata);
			$Porder -> save(array('tuan_id' => $tuan_id, 'tuan_status' => 3, 'order_status' => 3));
			D('Ptuan') -> save(array('id' => $tuan_id, 'tuan_status' => 3, 'success_time' => time()));
		}
		$showBtnBox = 1;
		$user_id = $this -> uid;
		if ($user_id == $tuanInfo['user_id']) {
			$showBtnBox = 2;
		} else {
			$tuanUserTeamTmp = D('Ptuanteam') -> where(array('tuan_id' => $tuan_id, 'user_id' => $this -> uid,'tuan_status' =>2)) -> find();
			if ($tuanUserTeamTmp) {
				$orderInfo = $Porder -> where(array('order_id' => $tuanUserTeamTmp['order_id'], 'user_id' => $this -> uid)) -> find();
				if ($orderInfo && $orderInfo['order_status'] == 6) {
					$showBtnBox = 4;
				} else {
					$showBtnBox = 3;
				}
			} else {
				$showBtnBox = 4;
			}
		}
		if ($tuanInfo['tuan_status'] != 2) {
			$showBtnBox = 5;
		}
		$daojishiTimes = $tuanInfo['tuan_time'] + $goods['tuan_hours'] * 3600 - time();
		if ($daojishiTimes <= 0 && $tuanInfo['tuan_status'] != 3) {
			$pguoqidata = array('tuan_status' => '4', 'order_status' => '7');
			D('Porder') -> where(array('tuan_id' => $tuan_id, array('tstatus', array('NEQ', 0)))) -> setField($pguoqidata);
			//$Porder -> save(array('tuan_status' => 4,'order_status' => 7), array('where' => array('tuan_id' => $tuan_id)));
			D('Ptuan') -> save(array('id' => $tuan_id, 'tuan_status' => 4));
			$daojishiTimes = 0;
			$showBtnBox = 6;
		}
		$this -> assign('users', D('Users') -> itemsByIds($ids));
		$this -> assign('lists', $lists);
		$this -> assign('shengyu', $shengyu);
		$this -> assign('tuanInfo', $tuanInfo);
		$this -> assign('goods', $goods);
		$this -> assign('tusers', $tusers);
		$this -> assign('tuan_num', $tuan_num);
		$this -> assign('tuan_price', $tuan_price);
		$this -> assign('daojishiTimes', $daojishiTimes);
		$this -> assign('showBtnBox', $showBtnBox);
		$this -> display();
	}
	
	  public function shop() {
        $id = (int) $this->_param('id');
        if (!$detail = D('Pshop')->find($id)) {
            $this->error('没有该商家');
            die;
        }
       
        $this->assign('detail',$detail);
        $this->display();
    }
}
