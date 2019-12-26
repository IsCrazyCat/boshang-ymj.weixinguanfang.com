<?php
class EmailModel extends CommonModel {
    protected $pk = 'email_id';
    protected $tableName = 'email';
    protected $token = 'email';
    protected $mailobj = null;
	protected $_CONFIG = array();

    protected function _initialize(){
        $this->_CONFIG = D('Setting')->fetchAll();
	}
	
    public function fetchAll() {
        $cache = cache(array('type' => 'File', 'expire' => $this->cacheTime));
        if (!$data = $cache->get($this->token)) {
            $result = $this->order($this->orderby)->select();
            $data = array();
            foreach ($result as $row) {
                $data[$row['email_key']] = $row;
            }
            $cache->set($this->token, $data);
        }
        return $data;
    }

    public function sendMail($code, $email, $title, $datas) {
        $tmpl = $this->fetchAll();
        if (!empty($tmpl[$code]['is_open'])) {
            $content = $tmpl[$code]['email_tmpl'];
            $config = D('Setting')->fetchAll();
            $datas['sitename'] = $config['site']['sitename'];
            $datas['tel'] = $config['site']['tel'];

            foreach ($datas as $k => $val) {
                $content = str_replace('{' . $k . '}', $val, $content);
            }
            if ($this->mailobj == null) {
                $this->mailobj = $this->mail($config);
            }
            if (is_array($email)) {
                foreach ($email as $m) {
                    $this->mailobj->addAddress($m);
                }
            } else {
                $this->mailobj->addAddress($email);
            }
            $this->mailobj->Subject = $title;
            $this->mailobj->Body = $content;
            return $this->mailobj->send();
        }
        return false;
    }

    private function mail($config) {
        Vendor("phpmailer.PHPMailerAutoload");
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $config['mail']['smtp'];
        $mail->SMTPAuth = true;
        $mail->CharSet = "utf-8";
        $mail->Username = $config['mail']['username'];
        $mail->Password = $config['mail']['password'];
        $mail->Port = $config['mail']['port'];
        $mail->From = $config['mail']['from'];
        $mail->FromName = $config['site']['sitename'];
        $mail->isHTML(true);
        return $mail;
    }

    public function getEorrer() {
        return $this->mailobj->ErrorInfo;
    }
	//抢单批量发送邮箱，又用不上
	public function emailTZdelivery($order_id){
        if(is_numeric($order_id) &&  ($order_id = (int)$order_id)){
           $order_id = array($order_id); 
        }
        $deliveryOrders = D('DeliveryOrder')->itemsByIds($order_id);//取出物流订单的id，这个id是程序中传过来的，订单被抢，邮件通知购买者
        $user_ids = array();
        foreach($deliveryOrders as $val){
            $user_ids[$val['user_id']] =$val['user_id'];             
        }
        $uesr = D('Users')->itemsByIds($user_ids);//查到下单人的网站id
		$uesr_email = $uesr['email'];//购买者的邮箱
        if(!empty($uesr_email)){		
			D('Email')->sendMail('email_tz_delivery', $uesr_email, '您的订单即将进行配送', array(
				'name'=>$data['name'],
				'date'=>$data['date']
				));
		}				
        return true;
    }

	//审核友情链接发送邮件
	public function send_email_link_audit($link_id){
        $obj = D('Links');
		$detail = $obj->find($link_id);
		$t = time();
        $date = date('Y-m-d H:i:s ', $t);
        if(!empty($detail['link_email'])){		
			D('Email')->sendMail('send_email_link_audit', $detail['link_email'], '您申请的友情链接已审核通过', array(
				'link_name'=>$detail['link_name'],
				'link_url'=>$detail['link_url'],
				'link_audit_time'=>$date
				));
		}				
        return true;
    }
    

}