<?php


//这个分组不需要再配置文件增加，如果增加了就可以UCENTER可以访问，这样不友好
class IndexAction extends Action{
    private $passport = null;
    private $ucget = null;
    private $ucpost = null;
    protected  function _initialize(){
        $_GET['code'] = empty($_GET['code']) ? die('0'):$_GET['code'];
        $this->passport = D('Passport');  
        parse_str(uc_authcode($_GET['code'], 'DECODE', UC_KEY), $this->ucget);    
        include_once BASE_PATH.'/api/uc_client/lib/xml.class.php';
	$this->ucpost = xml_unserialize(file_get_contents('php://input'));
       
    }
    public function  index(){
 
        if(in_array($this->ucget['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcredit', 'getcreditsettings', 'updatecreditsettings', 'addfeed'))) {
            $action = $this->ucget['action'];
            $this->$action();
	} else {
            die('0');
	}
    }
    
    private function synlogin(){
        $user = D('Users')->getUserByUcId($this->ucget['uid']);
        if($user){
            session('uid',$user['user_id']);
        }
    }
    
    private function synlogout(){
        session('uid','');
    }

    private  function test(){
        die('1');
    }
    
}