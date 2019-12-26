<?php
class PorderAction extends CommonAction {
	public function index() {
		$Order = D('Porder');
		import('ORG.Util.Page');
		// 导入分页类
		$keyword = $this -> _param('keyword', 'htmlspecialchars');
		if ($keyword) {
			$map['order_no'] = array('LIKE', '%' . $keyword . '%');
			$this -> assign('keyword', $keyword);
		}
		if (isset($_GET['ktt']) || isset($_POST['ktt'])) {
			$ktt = (int)$this -> _param('ktt');
			if ($ktt != 999) {
				$map['tuan_status'] = $ktt;
			}
			$this -> assign('ktt', $ktt);
		} else {
			$this -> assign('ktt', 999);
		}
		if (isset($_GET['ddt']) || isset($_POST['ddt'])) {
			$ddt = (int)$this -> _param('ddt');
			if ($ddt != 999) {
				$map['tstatus'] = $ddt;
			}
			$this -> assign('ddt', $ddt);
		} else {
			$this -> assign('ddt', 999);
		}
		if (isset($_GET['tst']) || isset($_POST['tst'])) {
			$tst = (int)$this -> _param('tst');
			if ($tst != 999) {
				$map['order_status'] = $tst;
			}
			$this -> assign('tst', $tst);
		} else {
			$this -> assign('tst', 999);
		}
		if (($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))) {
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$map['order_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
			$this -> assign('bg_date', $bg_date);
			$this -> assign('end_date', $end_date);
		} else {
			if ($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) {
				$bg_time = strtotime($bg_date);
				$this -> assign('bg_date', $bg_date);
				$map['order_time'] = array('EGT', $bg_time);
			}
			if ($end_date = $this -> _param('end_date', 'htmlspecialchars')) {
				$end_time = strtotime($end_date);
				$this -> assign('end_date', $end_date);
				$map['order_time'] = array('ELT', $end_time);
			}
		}
		if ($user_id = (int)$this -> _param('user_id')) {
			$users = D('Users') -> find($user_id);
			$this -> assign('nickname', $users['nickname']);
			$this -> assign('user_id', $user_id);
			$map['user_id'] = $user_id;
		}
		if ($tuan_id = (int)$this -> _param('tuan_id')) {
			$this -> assign('tuan_id', $tuan_id);
			$map['tuan_id'] = $tuan_id;
		}
		if ($shop_id = (int)$this -> _param('shop_id')) {
			$map['shop_id'] = $shop_id;
			$shop = D('Pshop') -> find($shop_id);
			$this -> assign('shop_name', $shop['name']);
			$this -> assign('shop_id', $shop_id);
		}
		if (isset($_GET['pay_id']) || isset($_POST['pay_id'])) {
			$pay_id = (int)$this -> _param('pay_id');
			if ($pay_id == 1) {
				$map['pay_name'] = weixin;
			} elseif ($pay_id == 2) {
				$map['pay_name'] = alipay;
			}
			$this -> assign('pay_id', $pay_id);
		} else {
			$this -> assign('pay_id', 999);
		}
		$count = $Order -> where($map) -> count();
		$Page = new Page($count, 15);
		$show = $Page -> show();
		$list = $Order -> where($map) -> order(array('id' => 'desc')) -> limit($Page -> firstRow . ',' . $Page -> listRows) -> select();
		$user_ids = $order_ids = $shop_ids = $addr_ids = array();
		foreach ($list as $key => $val) {
			$user_ids[$val['user_id']] = $val['user_id'];
		}

		$daoTimes = time() - 86400;
		$guoqi['order_time'] = array('elt', $daoTimes);
		$guoqi['order_status'] = 1;
		D('Porder') -> where($guoqi) -> setField(array('order_status' => 6));
		session('map', $map);
		$this -> assign('users', D('Users') -> itemsByIds($user_ids));
		$this -> assign('tstatusArray', D('Porder') -> getTstatus());
		$this -> assign('orderStatusArray', D('Porder') -> getorderStatus());
		$this -> assign('tuanStatusArray', D('Porder') -> gettuanStatus());
		$this -> assign('list', $list);
		$this -> assign('page', $show);
		$this -> display();
		// 输出模板
	}

	public function fahuo($id = 0) {
		if (is_numeric($id) && ($id = (int)$id)) {
			$obj = D('Porder');
			if (!$detail = $obj -> find($id)) {
				$this -> baoError('请选择要发货的商品');
			}
			if ($this -> isPost()) {
				$data['id'] = $id;
				$data['express_no'] = (int)$this -> _post('express_no');
				$data['express_time'] = time();
				$data['order_status'] = 4;
				if (false !== $obj -> save($data)) {
					$this -> baoSuccess('发货成功', U('porder/index'));
					$uid = $detail['user_id'];
					include_once "Baocms/Lib/Net/Wxmesg.class.php";
					$_data_fahuo = array(//整体变更
					'url' => "http://" . $_SERVER['HTTP_HOST'] . "/mcenter/pintuan/order/id/" . $id . ".html", 'topcolor' => '#F55555', 'first' => '嗖嗖嗖~您的拼团订单已经发货啦~', 'goodsName' => $detail['goods_name'], 'kuaidi' => $detail['express_name'], 'kuaididanhao' => $data['express_no'], 'dizhi' => $detail['address'] . " " . $detail['xm'] . " " . $detail['tel'], 'remark' => '请您耐心等候，发现质量问题，请及时联系客服：' . $this -> CONFIG['site']['tel'], );
					$fahuo_data = Wxmesg::fahuo($_data_fahuo);
					$return = Wxmesg::net($uid, 'OPENTM202243318', $fahuo_data);
				}
				$this -> baoError('操作失败');
			} else {
				$this -> assign('detail', $detail);
				$this -> assign('tstatusArray', $obj -> getTstatus());
				$this -> display();
			}
		} else {
			$ids = $this -> _post('id', false);
			$flag = false;
			if (is_array($ids)) {
				$obj = D('Porder');
				$succ_result = 0;
				$error_result = 0;
				foreach ($ids as $fa_id) {
					$detail = $obj -> find($fa_id);
					if ($detail['order_status'] == 9 && $detail['express_name'] == '同城配送') {
						$flag = true;
						$res = D('Porder') -> save(array('id' => $fa_id, 'order_status' => 4));
						if ($res) {
							$succ_result += 1;
						} else {
							$error_result += 1;
						}
						$uid = $detail['user_id'];
						include_once "Baocms/Lib/Net/Wxmesg.class.php";
						$_data_fahuo = array(//整体变更
						'url' => "http://" . $_SERVER['HTTP_HOST'] . "/mcenter/pintuan/order/id/" . $fa_id . ".html", 'topcolor' => '#F55555', 'first' => '嗖嗖嗖~您的拼团订单已经发货啦~', 'goodsName' => $detail['goods_name'], 'kuaidi' => $detail['express_name'], 'kuaididanhao' => $data['express_no'], 'dizhi' => $detail['address'] . " " . $detail['xm'] . " " . $detail['tel'], 'remark' => '请您耐心等候，发现质量问题，请及时联系客服：' . $this -> CONFIG['site']['tel'], );
						$fahuo_data = Wxmesg::fahuo($_data_fahuo);
						$return = Wxmesg::net($uid, 'OPENTM202243318', $fahuo_data);

					}
				}
			}
			if ($flag) {
				$this -> baoSuccess('批量发货成功' . $succ_result . '条，失败' . $error_result . '条', U('porder/index'));
			} else {
				$this -> baoError('请选择同城配送订单', U('porder/index'));
			}
		}
	}

	public function order($id = 0) {
		if ($id = (int)$id) {
			$obj = D('Porder');
			if (!$detail = $obj -> find($id)) {
				$this -> baoError('请选择您要查看的商品详情');
			}
			if ($this -> isPost()) {
				$data['id'] = $id;
				$data['order_status'] = (int)$this -> _post('order_status');
				if ($detail['order_status'] == $data['order_status']) {
					$this -> baoSuccess('不需要修改', U('porder/index'));
				} else {
					if (false !== $obj -> save($data)) {
						$this -> baoSuccess('修改成功', U('porder/index'));
					}
				}
				$this -> baoError('操作失败');
			} else {
				$this -> assign('detail', $detail);
				$this -> assign('tstatusArray', $obj -> getTstatus());
				$this -> assign('orderStatusArray', $obj -> getorderStatus());
				$this -> display();
			}
		} else {
			$this -> baoError('请选择您要查看的商品详情');
		}
	}

	public function tuikuan($id = 0) {
		if (is_numeric($id) && ($id = (int)$id)) {
			$detail = D('Porder') -> find($id);
			$flag = false;
			if ($detail['order_status'] == 7 && !empty($detail['order_no']) && $detail['pay_name'] == 'weixin') {
				$payment = D('Payment') -> getPayment($detail['pay_name']);
				define('WEIXIN_APPID', $payment['appid']);
				define('WEIXIN_MCHID', $payment['mchid']);
				define('WEIXIN_APPSECRET', $payment['appsecret']);
				define('WEIXIN_KEY', $payment['appkey']);
				define('WEIXIN_SSLCERT_PATH', APP_PATH . 'Lib/Payment/cacert/apiclient_cert.pem');
				define('WEIXIN_SSLKEY_PATH', APP_PATH . 'Lib/Payment/cacert/apiclient_key.pem');
				define('WEIXIN_SSLCA_PATH', APP_PATH . 'Lib/Payment/cacert/rootca.pem');
				include (APP_PATH . 'Lib/Payment/weixin/WxPay.Api.php');
				$pay_price = $detail['pay_price'];
				$input = new WxPayRefund();
				$input -> SetOut_trade_no($detail['order_no']);
				$input -> SetTotal_fee($pay_price);
				$input -> SetRefund_fee($pay_price);
				$input -> SetOut_refund_no(WEIXIN_MCHID . date("YmdHis"));
				$input -> SetOp_user_id(WEIXIN_MCHID);
				$return = WxPayApi::refund($input);
				if (is_array($return) && $return['result_code'] == 'SUCCESS') {
					$flag = true;
					D('Porder') -> save(array('id' => $id, 'order_status' => 8));
					$this -> baoSuccess('退款成功', U('porder/index'));
					$uid = $detail['user_id'];
					include_once "Baocms/Lib/Net/Wxmesg.class.php";
					$_data_cttuikuan = array(//整体变更
					'url' => "http://" . $_SERVER['HTTP_HOST'] . "/mcenter/pintuan/order/id/" . $id . ".html", 'topcolor' => '#F55555', 'first' => '您的订单（因未成团）退款申请已经提交至微信处理了', 'payprice' => round($pay_price / 100, 2) . '元', 'orderno' => $detail['order_no'], 'remark' => '微信系统需要审核，预计最迟5个工作日内会退回您支付的帐号，感谢您的支持！', );
					$cttuikuan_data = Wxmesg::cttuikuan($_data_cttuikuan);
					$return = Wxmesg::net($uid, 'TM00004', $cttuikuan_data);
				} else {
					$this -> baoError('退款失败！');
				}
			} else if ($detail['order_status'] == 7 && !empty($detail['order_no']) && $detail['pay_name'] == 'wxapp') {
				$payment = D('Payment') -> getPayment($detail['pay_name']);
				define('WEIXIN_APPID', $payment['appid']);
				define('WEIXIN_MCHID', $payment['mchid']);
				define('WEIXIN_APPSECRET', $payment['appsecret']);
				define('WEIXIN_KEY', $payment['appkey']);
				define('WEIXIN_SSLCERT_PATH', APP_PATH . 'Lib/Payment/appcert/apiclient_cert.pem');
				define('WEIXIN_SSLKEY_PATH', APP_PATH . 'Lib/Payment/appcert/apiclient_key.pem');
				define('WEIXIN_SSLCA_PATH', APP_PATH . 'Lib/Payment/appcert/rootca.pem');
				include (APP_PATH . 'Lib/Payment/wxapp/WxPay.Api.php');
				$pay_price = $detail['pay_price'];
				$input = new WxPayRefund();
				$input -> SetOut_trade_no($detail['order_no']);
				$input -> SetTotal_fee($pay_price);
				$input -> SetRefund_fee($pay_price);
				$input -> SetOut_refund_no(WEIXIN_MCHID . date("YmdHis"));
				$input -> SetOp_user_id(WEIXIN_MCHID);
				$return = WxPayApi::refund($input);
				if (is_array($return) && $return['result_code'] == 'SUCCESS') {
					$flag = true;
					D('Porder') -> save(array('id' => $id, 'order_status' => 8));
					$this -> baoSuccess('退款成功', U('porder/index'));
					$uid = $detail['user_id'];
					include_once "Baocms/Lib/Net/Wxmesg.class.php";
					$_data_cttuikuan = array(//整体变更
					'url' => "http://" . $_SERVER['HTTP_HOST'] . "/mcenter/pintuan/order/id/" . $id . ".html", 'topcolor' => '#F55555', 'first' => '您的订单（因未成团）退款申请已经提交至微信处理了', 'payprice' => round($pay_price / 100, 2) . '元', 'orderno' => $detail['order_no'], 'remark' => '微信系统需要审核，预计最迟5个工作日内会退回您支付的帐号，感谢您的支持！', );
					$cttuikuan_data = Wxmesg::cttuikuan($_data_cttuikuan);
					$return = Wxmesg::net($uid, 'TM00004', $cttuikuan_data);
				} else {
					$this -> baoError('退款失败！');
				}
			} else {
				$this -> baoError('该订单未使用微信支付', U('porder/index'));
			}
		} else {
			$ids = $this -> _post('id', false);
			$flag = false;
			if (is_array($ids)) {
				$obj = D('Porder');
				foreach ($ids as $pay_id) {
					$detail = $obj -> find($pay_id);
					if ($detail['order_status'] == 7 && !empty($detail['order_no']) && $detail['pay_name'] == 'weixin') {
						$payment = D('Payment') -> getPayment($detail['pay_name']);
						define('WEIXIN_APPID', $payment['appid']);
						define('WEIXIN_MCHID', $payment['mchid']);
						define('WEIXIN_APPSECRET', $payment['appsecret']);
						define('WEIXIN_KEY', $payment['appkey']);
						define('WEIXIN_SSLCERT_PATH', APP_PATH . 'Lib/Payment/cacert/apiclient_cert.pem');
						define('WEIXIN_SSLKEY_PATH', APP_PATH . 'Lib/Payment/cacert/apiclient_key.pem');
						define('WEIXIN_SSLCA_PATH', APP_PATH . 'Lib/Payment/cacert/rootca.pem');
						include (APP_PATH . 'Lib/Payment/weixin/WxPay.Api.php');
						$pay_price = $detail['pay_price'];
						$input = new WxPayRefund();
						$input -> SetOut_trade_no($detail['order_no']);
						$input -> SetTotal_fee($pay_price);
						$input -> SetRefund_fee($pay_price);
						$input -> SetOut_refund_no(WEIXIN_MCHID . date("YmdHis"));
						$input -> SetOp_user_id(WEIXIN_MCHID);
						$return = WxPayApi::refund($input);
						if (is_array($return) && $return['result_code'] == 'SUCCESS') {
							$flag = true;
							$ordeross = 8;
							D('Porder') -> save(array('id' => $pay_id, 'order_status' => $ordeross));
							$uid = $detail['user_id'];
							include_once "Baocms/Lib/Net/Wxmesg.class.php";
							$_data_cttuikuan = array(//整体变更
							'url' => "http://" . $_SERVER['HTTP_HOST'] . "/mcenter/pintuan/order/id/" . $pay_id . ".html", 'topcolor' => '#F55555', 'first' => '您的订单（因未成团）退款申请已经提交至微信处理了', 'payprice' => round($pay_price / 100, 2) . '元', 'orderno' => $detail['order_no'], 'remark' => '微信系统需要审核，预计最迟5个工作日内会退回您支付的帐号，感谢您的支持！', );
							$cttuikuan_data = Wxmesg::cttuikuan($_data_cttuikuan);
							$return = Wxmesg::net($uid, 'TM00004', $cttuikuan_data);
						}
					} elseif ($detail['order_status'] == 7 && !empty($detail['order_no']) && $detail['pay_name'] == 'wxapp') {
						$payment = D('Payment') -> getPayment($detail['pay_name']);
						define('WEIXIN_APPID', $payment['appid']);
						define('WEIXIN_MCHID', $payment['mchid']);
						define('WEIXIN_APPSECRET', $payment['appsecret']);
						define('WEIXIN_KEY', $payment['appkey']);
						define('WEIXIN_SSLCERT_PATH', APP_PATH . 'Lib/Payment/appcert/apiclient_cert.pem');
						define('WEIXIN_SSLKEY_PATH', APP_PATH . 'Lib/Payment/appcert/apiclient_key.pem');
						define('WEIXIN_SSLCA_PATH', APP_PATH . 'Lib/Payment/appcert/rootca.pem');
						include (APP_PATH . 'Lib/Payment/wxapp/WxPay.Api.php');
						$pay_price = $detail['pay_price'];
						$input = new WxPayRefund();
						$input -> SetOut_trade_no($detail['order_no']);
						$input -> SetTotal_fee($pay_price);
						$input -> SetRefund_fee($pay_price);
						$input -> SetOut_refund_no(WEIXIN_MCHID . date("YmdHis"));
						$input -> SetOp_user_id(WEIXIN_MCHID);
						$return = WxPayApi::refund($input);
						if (is_array($return) && $return['result_code'] == 'SUCCESS') {
							$flag = true;
							$ordeross = 8;
							D('Porder') -> save(array('id' => $pay_id, 'order_status' => $ordeross));
							$uid = $detail['user_id'];
							include_once "Baocms/Lib/Net/Wxmesg.class.php";
							$_data_cttuikuan = array(//整体变更
							'url' => "http://" . $_SERVER['HTTP_HOST'] . "/mcenter/pintuan/order/id/" . $pay_id . ".html", 'topcolor' => '#F55555', 'first' => '您的订单（因未成团）退款申请已经提交至微信处理了', 'payprice' => round($pay_price / 100, 2) . '元', 'orderno' => $detail['order_no'], 'remark' => '微信系统需要审核，预计最迟5个工作日内会退回您支付的帐号，感谢您的支持！', );
							$cttuikuan_data = Wxmesg::cttuikuan($_data_cttuikuan);
							$return = Wxmesg::net($uid, 'TM00004', $cttuikuan_data);
						}
					}
				}
			}
			if ($flag) {
				$this -> baoSuccess('批量退款成功', U('porder/index'));
			} else {
				$this -> baoError('请选择微信支付订单', U('porder/index'));
			}
		}
	}

	public function export() {
		$orders = D('Porder') -> where($_SESSION['map']) -> order(array('id' => 'asc')) -> select();
		$date = date("Y_m_d", time());
		$filetitle = "宜鲜果坊拼团";
		$fileName = $filetitle . "_" . $date;
		/* 输入到CSV文件 */
		$html = "\xEF\xBB\xBF";
		/* 输出表头 */
		$filter = array('aa' => '序号', 'bb' => '年', 'cc' => '月', 'dd' => '日', 'ee' => '下单时间', 'ff' => '订单类型', 'gg' => '开团人数', 'hh' => '团ID', 'ii' => '订单编号', 'jj' => '商品名称', 'kk' => '商品价格（元）', 'll' => '运费（元）', 'mm' => '总价（元）', 'nn' => '订单状态', 'oo' => '支付方式', 'pp' => '支付时间', 'qq' => '快递名称', 'rr' => '快递单号', 'ss' => '收货地址', 'tt' => '省', 'uu' => '市', 'vv' => '县', 'ww' => '姓名', 'xx' => '电话', 'yy' => '用户备注', 'zz' => '信息备注');
		foreach ($filter as $key => $title) {
			$html .= iconv('utf-8', 'gbk', $title) . "\t,";
		}
		$html .= "\n";
		foreach ($orders as $k => $v) {
			if ($v['tstatus'] == '0') {
				$tstatus = '普通订单';
			}
			if ($v['tstatus'] == '1') {
				$tstatus = '开团订单';
			}
			if ($v['tstatus'] == '2') {
				$tstatus = '参团订单';
			}
			if ($v['tuan_status'] == '1') {
				$tuan_status = '未支付';
			}
			if ($v['tuan_status'] == '2') {
				$tuan_status = '已支付，拼团中';
			}
			if ($v['tuan_status'] == '3') {
				$tuan_status = '拼团成功';
			}
			if ($v['tuan_status'] == '4') {
				$tuan_status = '拼团失败';
			}
			if ($v['order_status'] == '1') {
				$order_status = '待支付';
			}
			if ($v['order_status'] == '2') {
				$order_status = '已支付';
			}
			if ($v['order_status'] == '3') {
				$order_status = '已确认，待发货';
			}
			if ($v['order_status'] == '4') {
				$order_status = '配送中';
			}
			if ($v['order_status'] == '5') {
				$order_status = '已签收';
			}
			if ($v['order_status'] == '6') {
				$order_status = '交易已取消';
			}
			if ($v['order_status'] == '7') {
				$order_status = '退款处理中';
			}
			if ($v['order_status'] == '8') {
				$order_status = '退款成功';
			}
			$adds = D('Paddress') -> find($v['address_id']);
			$sheng = D('Paddlist') -> find($adds['province_id']);
			$shi = D('Paddlist') -> find($adds['city_id']);
			$xiancheng = D('Paddlist') -> find($adds['area_id']);
			$time = date('H:i:s', $v['order_time']);
			$nian = date('Y', $v['order_time']);
			$yue = date('m', $v['order_time']);
			$riqi = date('d', $v['order_time']);
			$pay_time = date('H:i:s', $v['pay_time']);
			$filter = array('aa' => '序号', 'bb' => '年', 'cc' => '月', 'dd' => '日', 'ee' => '下单时间', 'ff' => '订单类型', 'gg' => '开团人数', 'hh' => '团ID', 'ii' => '订单编号', 'jj' => '商品名称', 'kk' => '商品价格（元）', 'll' => '运费（元）', 'mm' => '总价（元）', 'nn' => '订单状态', 'oo' => '支付方式', 'pp' => '支付时间', 'qq' => '快递名称', 'rr' => '快递单号', 'ss' => '收货地址', 'tt' => '省', 'uu' => '市', 'vv' => '县', 'ww' => '姓名', 'xx' => '电话', 'yy' => '用户备注', 'zz' => '信息备注');
			$orders[$k]['aa'] = $v['id'];
			$orders[$k]['bb'] = $nian;
			$orders[$k]['cc'] = $yue;
			$orders[$k]['dd'] = $riqi;
			$orders[$k]['ee'] = $time;
			$orders[$k]['ff'] = $tstatus;
			$orders[$k]['gg'] = $v['renshu'];
			$orders[$k]['hh'] = $v['tuan_id'];
			$orders[$k]['ii'] = $v['order_no'];
			$orders[$k]['jj'] = $v['goods_name'];
			$orders[$k]['kk'] = $v['goods_price'] / 100;
			$orders[$k]['ll'] = $v['express_price'] / 100;
			$orders[$k]['mm'] = $v['pay_price'] / 100;
			$orders[$k]['nn'] = $order_status;
			$orders[$k]['oo'] = $v['pay_name'];
			$orders[$k]['pp'] = $pay_time;
			$orders[$k]['qq'] = $v['express_name'];
			$orders[$k]['rr'] = $v['express_no'];
			$orders[$k]['ss'] = $v['address'];
			$orders[$k]['tt'] = $sheng['name'];
			$orders[$k]['uu'] = $shi['name'];
			$orders[$k]['vv'] = $xiancheng['name'];
			$orders[$k]['ww'] = $v['xm'];
			$orders[$k]['xx'] = $v['tel'];
			$orders[$k]['yy'] = $v['order_beizu'];
			foreach ($filter as $key => $title) {
				$html .= iconv('utf-8', 'gbk', $orders[$k][$key]) . "\t,";
			}
			$html .= "\n";
		}
		/* 输出CSV文件 */
		header("Content-type:text/csv");
		header("Content-Disposition:attachment; filename=$fileName.csv");
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo $html;
		exit();
	}

	public function plfahuo() {
		$file = $_FILES['fileName'];
		$max_size = "2000000";
		$fname = $file['name'];
		$ftype = strtolower(substr(strrchr($fname, '.'), 1));
		//文件格式
		$uploadfile = $file['tmp_name'];
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (is_uploaded_file($uploadfile)) {
				if ($file['size'] > $max_size) {
					$this -> baoError('导入的文件太大了');
					exit ;
				}
				if ($ftype == 'xls') {
					Vendor("PHPExcel.PHPExcel");
					$objReader = PHPExcel_IOFactory::createReader('Excel5');
					$objPHPExcel = $objReader -> load($uploadfile);
					$sheet = $objPHPExcel -> getSheet(0);
					$highestRow = $sheet -> getHighestRow();
					$succ_result = 0;
					$error_result = 0;
					for ($j = 2; $j <= $highestRow; $j++) {
						$orderNo = trim($objPHPExcel -> getActiveSheet() -> getCell("A$j") -> getValue());
						$expressOrder = trim($objPHPExcel -> getActiveSheet() -> getCell("B$j") -> getValue());
						if (!empty($expressOrder)) {
							$res = D('Porder') -> save(array('order_status' => 4, 'express_no' => $expressOrder, 'express_time' => time()), array('order_no' => $orderNo));
							if ($res) {
								$succ_result += 1;
							} else {
								$error_result += 1;
							}
							$order = D('Porder') -> where(array('order_no' => $orderNo)) -> find();
							/*发货提醒消息模板*/
							$uid = $order['user_id'];
							include_once "Baocms/Lib/Net/Wxmesg.class.php";
							$_data_fahuo = array(//整体变更
							'url' => "http://" . $_SERVER['HTTP_HOST'] . "/mcenter/pintuan/order/id/" . $id . ".html", 'topcolor' => '#F55555', 'first' => '嗖嗖嗖~您的拼团订单已经发货啦~', 'goodsName' => $order['goods_name'], 'kuaidi' => $order['express_name'], 'kuaididanhao' => $expressOrder, 'dizhi' => $order['address'] . " " . $order['xm'] . " " . $order['tel'], 'remark' => '请您耐心等候，发现质量问题，请及时联系客服：' . $this -> CONFIG['site']['tel'], );
							$fahuo_data = Wxmesg::fahuo($_data_fahuo);
							$return = Wxmesg::net($uid, 'OPENTM202243318', $fahuo_data);
							/*发货提醒模板消息*/
						} else {
							if (!empty($orderNo)) {
								$error_result += 1;
							}
						}
					}
					$this -> baoSuccess('导入发货订单操作成功！成功' . $succ_result . '条，失败' . $error_result . '条', U('porder/plfahuo'));
				} elseif ($ftype == 'csv') {
					if (empty($uploadfile)) {
						echo '请选择要导入的CSV文件！';
						$this -> baoError('请选择要导入的CSV文件');
						exit ;
					}
					$handle = fopen($uploadfile, 'r');
					$n = 0;
					while ($data = fgetcsv($handle, 10000)) {
						$num = count($data);
						for ($i = 0; $i < $num; $i++) {
							$out[$n][$i] = $data[$i];
						}
						$n++;
					}
					$result = $out;
					//解析csv
					$len_result = count($result);
					if ($len_result == 0) {
						$this -> baoError('没有任何数据');
						exit ;
					}
					$succ_result = 0;
					$error_result = 0;
					for ($i = 1; $i < $len_result; $i++) {//循环获取各字段值
						$orderNo = trim(iconv('gb2312', 'utf-8', $result[$i][0]));
						//中文转码
						if ($orderNo == '') {
							continue;
						}
						$expressOrder = trim(iconv('gb2312', 'utf-8', $result[$i][1]));
						if (!empty($expressOrder) && !empty($expressName)) {
							$res = D('Porder') -> save(array('order_status' => 4, 'express_no' => $expressOrder, 'express_time' => time()), array('order_no' => $orderNo));
							if ($res) {
								$succ_result += 1;
							} else {
								$error_result += 1;
							}
							$order = D('Porder') -> where(array('order_no' => $orderNo)) -> find();
							/*发货提醒消息模板*/
							$uid = $order['user_id'];
							include_once "Baocms/Lib/Net/Wxmesg.class.php";
							$_data_fahuo = array(//整体变更
							'url' => "http://" . $_SERVER['HTTP_HOST'] . "/mcenter/pintuan/order/id/" . $id . ".html", 'topcolor' => '#F55555', 'first' => '嗖嗖嗖~您的拼团订单已经发货啦~', 'goodsName' => $order['goods_name'], 'kuaidi' => $order['express_name'], 'kuaididanhao' => $expressOrder, 'dizhi' => $order['address'] . " " . $order['xm'] . " " . $order['tel'], 'remark' => '请您耐心等候，发现质量问题，请及时联系客服：' . $this -> CONFIG['site']['tel'], );
							$fahuo_data = Wxmesg::fahuo($_data_fahuo);
							$return = Wxmesg::net($uid, 'OPENTM202243318', $fahuo_data);
							/*发货提醒模板消息*/
						} else {
							$error_result += 1;
						}
					}
					fclose($handle);
					$this -> baoSuccess('导入发货订单操作成功！成功' . $succ_result . '条，失败' . $error_result . '条', U('porder/plfahuo'));
					//关闭指针
				} else {
					$this -> baoError('文件后缀格式必须为xls或csv');
					exit ;
				}
			} else {
				$this -> baoError('批量退款成功');
				exit ;
			}
		}
	}

	public function pllist() {
		$Order = D('Porder');
		import('ORG.Util.Page');
		// 导入分页类
		$map['order_status'] = 9;
		if (isset($_GET['ktt']) || isset($_POST['ktt'])) {
			$ktt = (int)$this -> _param('ktt');
			if ($ktt != 999) {
				if ($ktt == 1) {
					$keyword = '顺丰快递';
				} elseif ($ktt == 2) {
					$keyword = '申通快递';
				} else {
					$keyword = '同城配送';
				}
				$map['express_name'] = array('LIKE', '%' . $keyword . '%');
			}
			$this -> assign('ktt', $ktt);
		} else {
			$this -> assign('ktt', 999);
		}
		if (($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))) {
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$map['order_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
			$this -> assign('bg_date', $bg_date);
			$this -> assign('end_date', $end_date);
		} else {
			if ($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) {
				$bg_time = strtotime($bg_date);
				$this -> assign('bg_date', $bg_date);
				$map['order_time'] = array('EGT', $bg_time);
			}
			if ($end_date = $this -> _param('end_date', 'htmlspecialchars')) {
				$end_time = strtotime($end_date);
				$this -> assign('end_date', $end_date);
				$map['order_time'] = array('ELT', $end_time);
			}
		}
		if ($tuan_id = (int)$this -> _param('tuan_id')) {
			$this -> assign('tuan_id', $tuan_id);
			$map['tuan_id'] = $tuan_id;
		}
		$count = $Order -> where($map) -> count();
		$Page = new Page($count, 15);
		$show = $Page -> show();
		$list = $Order -> where($map) -> order(array('id' => 'desc')) -> limit($Page -> firstRow . ',' . $Page -> listRows) -> select();
		$user_ids = $order_ids = $shop_ids = $addr_ids = array();
		foreach ($list as $key => $val) {
			$user_ids[$val['user_id']] = $val['user_id'];
		}
		session('map', $map);
		$this -> assign('users', D('Users') -> itemsByIds($user_ids));
		$this -> assign('tstatusArray', D('Porder') -> getTstatus());
		$this -> assign('orderStatusArray', D('Porder') -> getorderStatus());
		$this -> assign('tuanStatusArray', D('Porder') -> gettuanStatus());
		$this -> assign('list', $list);
		$this -> assign('page', $show);
		$this -> display();
	}

	public function lists($goods_id = 0) {
		if (is_numeric($goods_id) && ($goods_id = (int)$goods_id)) {
			$Order = D('Porder');
			import('ORG.Util.Page');
			$map['goods_id'] = $goods_id;
			if (($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))) {
				$bg_time = strtotime($bg_date);
				$end_time = strtotime($end_date);
				$map['order_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
				$this -> assign('bg_date', $bg_date);
				$this -> assign('end_date', $end_date);
			} else {
				if ($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) {
					$bg_time = strtotime($bg_date);
					$this -> assign('bg_date', $bg_date);
					$map['order_time'] = array('EGT', $bg_time);
				}
				if ($end_date = $this -> _param('end_date', 'htmlspecialchars')) {
					$end_time = strtotime($end_date);
					$this -> assign('end_date', $end_date);
					$map['order_time'] = array('ELT', $end_time);
				}
			}
			$count = $Order -> where($map) -> count();
			$Page = new Page($count, 15);
			$show = $Page -> show();
			$list = $Order -> where($map) -> order(array('id' => 'desc')) -> limit($Page -> firstRow . ',' . $Page -> listRows) -> select();
			$user_ids = array();
			foreach ($list as $key => $val) {
				$user_ids[$val['user_id']] = $val['user_id'];
			}
			session('map', $map);
			$this -> assign('users', D('Users') -> itemsByIds($user_ids));
			$this -> assign('tstatusArray', D('Porder') -> getTstatus());
			$this -> assign('orderStatusArray', D('Porder') -> getorderStatus());
			$this -> assign('tuanStatusArray', D('Porder') -> gettuanStatus());
			$this -> assign('list', $list);
			$this -> assign('page', $show);
			$this -> display();
		} else {
			$this -> baoError('请选择您要查看的商品订单');
		}

	}

	public function tuan() {
		$Order = D('Porder');
		import('ORG.Util.Page');
		// 导入分页类
		$map['order_status'] = array('IN', array(3, 4, 5, 9));
		$map['tstatus'] = array('IN', array(0, 1));
		if (isset($_GET['ktt']) || isset($_POST['ktt'])) {
			$ktt = (int)$this -> _param('ktt');
			if ($ktt != 999) {
				if ($ktt == 1) {
					$keyword = '顺丰快递';
				} elseif ($ktt == 2) {
					$keyword = '申通快递';
				} else {
					$keyword = '同城配送';
				}
				$map['express_name'] = array('LIKE', '%' . $keyword . '%');
			}
			$this -> assign('ktt', $ktt);
		} else {
			$this -> assign('ktt', 999);
		}
		if (isset($_GET['ddt']) || isset($_POST['ddt'])) {
			$ddt = (int)$this -> _param('ddt');
			if ($ddt != 999) {
				$map['tstatus'] = $ddt;
			}
			$this -> assign('ddt', $ddt);
		} else {
			$this -> assign('ddt', 999);
		}
		if (isset($_GET['tst']) || isset($_POST['tst'])) {
			$tst = (int)$this -> _param('tst');
			if ($tst != 999) {
				$map['order_status'] = $tst;
			}
			$this -> assign('tst', $tst);
		} else {
			$this -> assign('tst', 999);
		}
		if (($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) && ($end_date = $this -> _param('end_date', 'htmlspecialchars'))) {
			$bg_time = strtotime($bg_date);
			$end_time = strtotime($end_date);
			$map['order_time'] = array( array('ELT', $end_time), array('EGT', $bg_time));
			$this -> assign('bg_date', $bg_date);
			$this -> assign('end_date', $end_date);
		} else {
			if ($bg_date = $this -> _param('bg_date', 'htmlspecialchars')) {
				$bg_time = strtotime($bg_date);
				$this -> assign('bg_date', $bg_date);
				$map['order_time'] = array('EGT', $bg_time);
			}
			if ($end_date = $this -> _param('end_date', 'htmlspecialchars')) {
				$end_time = strtotime($end_date);
				$this -> assign('end_date', $end_date);
				$map['order_time'] = array('ELT', $end_time);
			}
		}
		$count = $Order -> where($map) -> count();
		$Page = new Page($count, 15);
		$show = $Page -> show();
		$list = $Order -> where($map) -> order(array('id' => 'desc')) -> limit($Page -> firstRow . ',' . $Page -> listRows) -> select();
		$user_ids = $tuan_ids = array();
		foreach ($list as $key => $val) {
			$user_ids[$val['user_id']] = $val['user_id'];
			$tuan_ids[$val['id']] = $val['tuan_id'];
		}
		session('map', $map);
		$this -> assign('users', D('Users') -> itemsByIds($user_ids));
		$this -> assign('tuans', D('Ptuan') -> itemsByIds($tuan_ids));
		$this -> assign('tstatusArray', D('Porder') -> getTstatus());
		$this -> assign('orderStatusArray', D('Porder') -> getorderStatus());
		$this -> assign('tuanStatusArray', D('Porder') -> gettuanStatus());
		$this -> assign('list', $list);
		$this -> assign('page', $show);
		$this -> display();
	}

	public function dayin() {
		$id = (int)$this -> _get('id');
		$tuan_id = (int)$this -> _get('tuan_id');
		if (!empty($tuan_id)) {
			$lists = D('Porder') -> where(array('tuan_id' => $tuan_id, 'order_status' => 3)) -> order(array('id' => 'asc')) -> select();
		} else {
			$lists = D('Porder') -> find($id);
			$dandu = 1;
		}
		D('Porder') -> where(array('tuan_id' => $tuan_id, 'order_status' => 3)) -> save(array('order_status' => 9));
		$this -> assign('lists', $lists);
		$this -> assign('dandu', $dandu);
		$this -> assign('tuans', D('Ptuan') -> find($tuan_id));
		$this -> assign('tstatusArray', D('Porder') -> getTstatus());
		$this -> assign('orderStatusArray', D('Porder') -> getorderStatus());
		$this -> assign('tuanStatusArray', D('Porder') -> gettuanStatus());
		$this -> display();
	}

	public function tuanorder() {
		$tuan_id = (int)$this -> _get('tuan_id');
		$map['tuan_id'] = $tuan_id;
		$lists = D('Porder') -> where($map) -> order(array('id' => 'asc')) -> select();
		session('map', $map);
		$this -> assign('lists', $lists);
		$this -> assign('tuans', D('Ptuan') -> find($tuan_id));
		$this -> assign('tstatusArray', D('Porder') -> getTstatus());
		$this -> assign('orderStatusArray', D('Porder') -> getorderStatus());
		$this -> assign('tuanStatusArray', D('Porder') -> gettuanStatus());
		$this -> display();
	}
}
