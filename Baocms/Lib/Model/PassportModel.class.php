<?php
class PassportModel {
    private $CONFIG = array();
    private $charset = 0;
    private $isuc = false;
    private $error = null; //如果存在错误的时候返回一下错误
    private $domain = '@qq.com'; //可以修改
    private $token = array();//手机APP 需要的 access_token
    private $user  = array();
    private $_CONFIG = array();

    public function __construct() {
        $config = D('Setting')->fetchAll();
        $this->_CONFIG = $config;
        if ($config['site']['ucenter']) {
            $this->isuc = true;
        }
        $this->CONFIG = $config['ucenter'];
        $this->charset = $this->CONFIG['charset'];
        if ($this->isuc) {
            $this->ucinit();
        }
    }
    
    public function getToken(){
        return $this->token;
    }

    public function getUserInfo(){
        return $this->user;
    }
    
    public function getError() {
        return $this->error;
    }

    public function ucinit() {
        define('UC_CONNECT', $this->CONFIG['UC_CONNECT']);
        define('UC_DBHOST', $this->CONFIG['UC_DBHOST']);
        define('UC_DBUSER', $this->CONFIG['UC_DBUSER']);
        define('UC_DBPW', $this->CONFIG['UC_DBPW']);
        define('UC_DBNAME', $this->CONFIG['UC_DBNAME']);
        define('UC_DBCHARSET', $this->CONFIG['UC_DBCHARSET']);
        define('UC_DBTABLEPRE', $this->CONFIG['UC_DBTABLEPRE']);
        define('UC_DBCONNECT', $this->CONFIG['UC_DBCONNECT']);
        define('UC_KEY', $this->CONFIG['UC_KEY']);
        define('UC_API', $this->CONFIG['UC_API']);
        define('UC_CHARSET', $this->CONFIG['UC_CHARSET']);
        define('UC_IP', $this->CONFIG['UC_IP']);
        define('UC_APPID', $this->CONFIG['UC_APPID']);
        define('UC_PPP', $this->CONFIG['UC_PPP']);
        require BASE_PATH . '/api/uc_client/client.php';
    }

    public function logout() {
        clearUid();
        if ($this->isuc) {
            uc_user_synlogout();
        }
        return true;
    }

    public function uppwd($account, $oldpwd, $newpwd) {
        if ($this->isuc) {
            if (isMobile($account)) {
                $ucresult = uc_user_edit($account, $oldpwd, $newpwd, '', 1);
            } elseif (isEmail($account)) {
                $local = explode('@', $account);
                $ucresult = uc_user_edit($local[0], $oldpwd, $newpwd, '', 1);
            }
            if ($ucresult == -1) {
                $this->error = '旧密码不正确';
                return false;
            }
        }
        $user = D('Users')->getUserByAccount($account);
        return D('Users')->save(array('user_id' => $user['user_id'], 'password' => md5($newpwd),'is_lock'=>0,'lock_num'=>0,'is_lock_time'=>''));
    }
	//设置支付密码
	public function set_pay_password($account, $pay_password) {
        $user = D('Users')->getUserByAccount($account);
        return D('Users')->save(array('user_id' => $user['user_id'], 'pay_password' => md5(md5($pay_password)),'is_lock'=>0,'lock_num'=>0,'is_lock_time'=>''));
    }


    //UC用邮件登录
    public function login($account, $password) {
        $this->token = array(
            'token' => md5(uniqid())
        );
        if ($this->isuc) {
            if (isMobile($account)) {
                list($uid, $username, $password2, $email) = uc_user_login($account, $password);
            } elseif (isEmail($account)) {
                $local = explode('@', $account);
                list($uid, $username, $password2, $email) = uc_user_login($local[0], $password);
            } else { //论坛的账户怎么办呢
                if ($this->charset) { //如果要转化成GBK的就
                    $account1 = iconv("UTF-8", "GB2312//IGNORE", $account);
                } else {
                    $account1 = $account;
                }
                list($uid, $username, $password2, $email) = uc_user_login($account1, $password);
            }
            if ($uid > 0) {
				if (isMobile($account)) {
					$user = D('Users')->getUserByMobile($account);
				} else {
					$user = D('Users')->getUserByAccount($account);
				}
  
                if ($user['closed'] == 1) {
                    $this->error = '用户不存在或被删除！';
                    return false;
                }
                $ip = get_client_ip();
                if (empty($user)) {
                    $data = array(
                        'ext0' => $account,
                        'account' => $account,
                        'password' => md5($password),
                        'nickname' => $account,
                        'reg_time' => NOW_TIME,
                        'reg_ip' => $ip,
                        'uc_id' => $uid,
                        'last_time' => NOW_TIME,
                        'last_ip' => $ip,
                        'token' => $this->token['token'],
                    );
                    $user['user_id'] = D('Users')->add($data);
                    D('Users')->prestige($user['user_id'], 'login');
                } else {
                    $data = array(
                        'last_time' => NOW_TIME,
                        'last_ip' => $ip,
                        'ext0' => $account,
                        'password' => md5($password),
                        'uc_id' => $uid,
                        'user_id' => $user['user_id'],
                        'token' => $this->token['token'],
                    );
                    D('Users')->save($data);
                    if (date('Y-m-d', $user['last_time']) < TODAY) {
                        D('Users')->prestige($user['user_id'], 'login');
                    }
                }
                setUid($user['user_id']);
                uc_user_synlogin($uid);
            } else {
                switch ($uid) {
                    case -1:
                        $this->error = '用户不存在,或者被删除';
                        break;
                    case -2:
                        $this->error = '密码错误';
                        break;
                    default :
                        $this->error = '联合登录失败';
                        break;
                }
                return false;
            }
        } else {
			if (isMobile($account)) {
            	$user = D('Users')->getUserByMobile($account);
			} else {
				$user = D('Users')->getUserByAccount($account);
			}
            if (empty($user)) {
                $this->error = '账号或密码不正确';
                return false;
            }
            if ($user['closed'] == 1) {
                $this->error = '用户不存在或被删除！';
                return false;
            }
			
			$db_user = D('Users');
			$is_lock = $db_user ->where(array('user_id'=>$user['user_id']))-> find();
			
			if ($is_lock['is_lock'] == 1){
				$present_time = NOW_TIME;//当前时间
				$cha = $this->_CONFIG['register']['register_is_lock_time'];
			    $present_time_cha = $present_time - $is_lock['is_lock_time'];
				if ($present_time_cha < $cha) {
					 $echo_time = $cha - $present_time_cha;
					 $this->error = '您的账户已经被锁定，请'.$echo_time.'秒后登陆';
					 return false;
			    }
            }		
			
            if ($user['password'] != md5($password)) {
				$config_lock_num = $this->_CONFIG['register']['register_lock_numl'];
				$config_lock_num_cha = $config_lock_num-1;
				$db_user->where(array('user_id' => $is_lock['user_id']))->setInc('lock_num',1);  
				if ($is_lock['lock_num'] >= $config_lock_num_cha){
					$db_user->save(array('user_id' => $is_lock['user_id'],'is_lock'=>1,'is_lock_time'=>NOW_TIME));
					$this->error = '您的账户已经被锁定，请15分钟后登陆';
					return false;
           		}
				$is_lock_cha = $config_lock_num -$is_lock['lock_num'];
				if ($is_lock['lock_num'] >= 1){
					$this->error = '请输入正确的账户密码，您还有'.$is_lock_cha.'次机会';
                	return false;
				}else{
					$this->error = '账号或密码不正确！';
                	return false;
				}
				
            }
						
            if (date('Y-m-d', $user['last_time']) < TODAY) {
                D('Users')->prestige($user['user_id'], 'login');
            }
            $data = array(
                'last_time' => NOW_TIME,
                'last_ip' => get_client_ip(),
                'user_id' => $user['user_id'],
                'token' => $this->token['token'],
            );
            $db_user->save($data);//登陆成功
			$db_user->save(array('user_id' => $is_lock['user_id'],'is_lock'=>0,'lock_num'=>0,'is_lock_time'=>''));//登陆成功后
            setUid($user['user_id']);
        }
        $connect = session('connect');
        if (!empty($connect)) {
            D('Connect')->save(array('connect_id' => $connect, 'uid' => $user['user_id']));
        }
        $this->user = $user;
        $this->token['uid'] = $user['user_id'];
        return true;
    }

    public function register($data = array()) {
        $this->token = array(
            'token' => md5(uniqid())
        );
        $data['reg_time'] = NOW_TIME;
        $data['reg_ip'] = get_client_ip();
        $invite_id = (int)cookie('invite_id');
        $obj = D('Users');
        if(!empty($invite_id)){
            $userinvite = $obj->find($invite_id);
            if(!empty($userinvite)){ //讲新的 推广员身份给创建账号的
                $data['invite6'] = $invite_id;
                $data['invite5'] = $userinvite['invite6'];
                $data['invite4'] = $userinvite['invite5'];
                $data['invite3'] = $userinvite['invite4'];
                $data['invite2'] = $userinvite['invite3'];
                $data['invite1'] = $userinvite['invite2'];
            }
        }
        $fuid = (int)cookie('fuid');
        if(empty($fuid)){
            if(!empty($data['fuid'])){
                $fuid = $data['fuid'];
            }else{
                $fuid =(int)session('fuid');
            }
        }
        $fuser = $obj->find($fuid);
        if ($fuser) {
            $data['fuid1'] = $fuser['user_id'];
            $data['fuid2'] = $fuser['fuid1'];
            $data['fuid3'] = $fuser['fuid2'];
            $profit_integral1 = (int)$this->_CONFIG['profit']['profit_integral1'];
            $profit_integral2 = (int)$this->_CONFIG['profit']['profit_integral2'];
            $profit_integral3 = (int)$this->_CONFIG['profit']['profit_integral3'];
            $intro = '推荐用户注册奖励积分';
			$flag = false;
            if ($profit_integral1) {
				$profit_min_rank_id = (int)$this->_CONFIG['profit']['profit_min_rank_id'];				
				if ($profit_min_rank_id) {
					$modelRank = D('Userrank');
					$rank = $modelRank->find($profit_min_rank_id);
					$userRank = $modelRank->find($fuser['rank_id']);
					if ($rank) {
						if ($userRank && $userRank['prestige'] >= $rank['prestige']) {
							$flag = true;
						}
						else {
							$flag = false;
						}
					}
					else {
						$flag = false;
					}
				}
				else {
					$flag = true;
				}
				if ($flag) {
					$obj->addIntegral($data['fuid1'], $profit_integral1, $intro);
				}
            }
            if ($profit_integral2 && $flag) {
                if ($data['fuid2']) {
                    $fuser2 = $obj->find($data['fuid2']);
                    if ($fuser2) {
                        $obj->addIntegral($data['fuid2'], $profit_integral2, $intro);
                    }
                }
            }
            if ($profit_integral3 && $flag) {
                if ($data['fuid3']) {
                    $fuser3 = $obj->find($data['fuid3']);
                    if ($fuser3) {
                        $obj->addIntegral($data['fuid3'], $profit_integral3, $intro);
                    }
                }
            }
        }

        if (empty($data))
            return false;
        if ($this->isuc) { //开启了UC
            if (isMobile($data['account'])) {
                $uid = uc_user_register($data['ext0'], $data['password'], $data['account'] . $this->domain); //这个@QQ.COM 可以自己更换
            } else {
                $uid = uc_user_register($data['ext0'], $data['password'], $data['account']);
            }

            if ($uid <= 0) {
                switch ($uid) {
                    case -1:
                        $this->error = '用户名不合法';
                        break;
                    case -2:
                        $this->error = '用户名包含不允许注册的词语';
                        break;
                    case -3:
                        $this->error = '用户名已经存在';
                        break;
                    case -4:
                        $this->error = 'Email 格式有误';
                        break;
                    case -5:
                        $this->error = 'Email 不允许注册';
                        break;
                    case -6:
                        $this->error = '该 Email 已经被注册';
                        break;
                }
                return false;
            }
            $data['uc_id'] = $uid;
            $data['password'] = md5($data['password']);
			
			if (isMobile($data['account'])) {
				$data['mobile'] = $data['account'];
			}

            $user = $obj->getUserByAccount($data['account']);
            $data['token'] = $this->token['token'];
            if ($user) {
                $data['user_id'] = $user['user_id'];
                $obj->save($data);
            } else {
                $data['user_id'] = $obj->add($data);
            }
        } else {

            $data['password'] = md5($data['password']);
            $user = $obj->getUserByAccount($data['account']);
            if ($user) {
                $this->error = '该账户已经存在';
                return false;
            }
			if (isMobile($data['account'])) {
				$data['mobile'] = $data['account'];
			}
            $data['user_id'] = $obj->add($data);
        }
        $this->token['uid'] = $data['user_id'];
        $connect = session('connect');
        if (!empty($connect)) {
            D('Connect')->save(array('connect_id' => $connect, 'uid' => $data['user_id']));
        }
        setUid($data['user_id']);
        return true;
    }
	//增加微信应用注册
	
	public function register2($data = array()) {
        $this->token = array(
            'token' => md5(uniqid())
        );
        $data['reg_time'] = NOW_TIME;
        $data['reg_ip'] = get_client_ip();
        $invite_id = (int)cookie('invite_id');
        if(!empty($invite_id)){
            $userinvite = D('Users')->find($invite_id);
            if(!empty($userinvite)){ //讲新的 推广员身份给创建账号的
                $data['invite6'] = $invite_id;
                $data['invite5'] = $userinvite['invite6'];
                $data['invite4'] = $userinvite['invite5'];
                $data['invite3'] = $userinvite['invite4'];
                $data['invite2'] = $userinvite['invite3'];
                $data['invite1'] = $userinvite['invite2'];
            }
        }
        if (empty($data))
            return false;
        if ($this->isuc) { //开启了UC
            if (isMobile($data['account'])) {
                $uid = uc_user_register($data['ext0'], $data['password'], $data['account'] . $this->domain); //这个@QQ.COM 可以自己更换
            } else {
                $uid = uc_user_register($data['ext0'], $data['password'], $data['account']);
            }

            if ($uid <= 0) {
                switch ($uid) {
                    case -1:
                        $this->error = '用户名不合法';
                        break;
                    case -2:
                        $this->error = '用户名包含不允许注册的词语';
                        break;
                    case -3:
                        $this->error = '用户名已经存在';
                        break;
                    case -4:
                        $this->error = 'Email 格式有误';
                        break;
                    case -5:
                        $this->error = 'Email 不允许注册';
                        break;
                    case -6:
                        $this->error = '该 Email 已经被注册';
                        break;
                }
                return false;
            }
            $data['uc_id'] = $uid;
            $data['password'] = md5($data['password']);
            $obj = D('Users');
            $user = $obj->getUserByAccount($data['account']);
            $data['token'] = $this->token['token'];
            if ($user) {
                $data['user_id'] = $user['user_id'];
                $obj->save($data);
            } else {
                $data['user_id'] = $obj->add($data);
            }
        } else {
            $obj = D('Users');
            $data['password'] = md5($data['password']);
            $user = $obj->getUserByAccount($data['account']);
            if ($user) {
                $this->error = '该账户已经存在';
                return false;
            }
            $data['user_id'] = $obj->add($data);
        }
        $this->token['uid'] = $data['user_id'];
        $connect = session('connect');
        if (!empty($connect)) {
            D('Connect')->save(array('connect_id' => $connect, 'uid' => $data['user_id']));
        }
        setUid($data['user_id']);
        return true;
    }

}