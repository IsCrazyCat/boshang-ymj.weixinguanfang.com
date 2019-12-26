<?php



class ExcelAction extends CommonAction {

    //读取excel数据 导入到数据库
    public function d_video()
    {
//        $res = $this->goods_import('E:/user.xlsx', '1','xlsx');//读取excel表中的数据
//        $res1 = $this->goods_import('E:/info.xlsx', '2','xlsx');//读取excel表中的数据
//        $res2 = $this->goods_import('E:/fenxiao.xlsx', '3','xlsx');//读取excel表中的数据
//

        $res = $this->goods_import(BASE_PATH .'/Baocms/Lib/Action/Wap/user.xlsx', '1','xlsx');//读取excel表中的数据
        $res1 = $this->goods_import(BASE_PATH .'/Baocms/Lib/Action/Wap/info.xlsx', '2','xlsx');//读取excel表中的数据
        $res2 = $this->goods_import(BASE_PATH .'/Baocms/Lib/Action/Wap/fenxiao.xlsx', '3','xlsx');//读取excel表中的数据
        exit($res1);
        die;
    }

    //读取excel表中的数据
    protected function goods_import($filename, $type='1',$exts = 'xls')
    {

        header("Content-Type:text/html;charset=utf-8");
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        Vendor("PHPExcel.Classes.PHPExcel");
        // Vendor('PHPExcel');
        //创建PHPExcel对象，注意，不能少了\
        $PHPExcel = new \PHPExcel();

        //如果excel文件后缀名为.xls，导入这个类
        if ($exts == 'xls') {
            Vendor("PHPExcel.Reader.Excel5");
            $PHPReader = new \PHPExcel_Reader_Excel5();

        } else if ($exts == 'xlsx') {
            Vendor("PHPExcel.Reader.Excel2007");
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        }
        //载入文件
        $PHPExcel = $PHPReader->load($filename, $encode = 'utf-8');

        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);
        //获取总列数
        $allColumn = $currentSheet->getHighestColumn();
        //获取总行数
        $allRow = $currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            //从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                //数据坐标
                $address = $currentColumn . $currentRow;
                //读取到的数据，保存到数组$arr中
                $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }

        }

        if($type==1){
            $r = $this->save_import($data);
        }else if($type==2){
            $r = $this->save_import2($data);
        }else if($type==3){
            $r = $this->save_import3($data);
        }
//        $this->changetime($data);
//        return 'success';
    }
    public function changetime($data){
        header("Content-Type:text/html;charset=utf-8");

        foreach ($data as $k => $v) {
            if($k == 1){
                continue;
            }
            $nickname = trim($v['B']);

            $openId = trim($v['D']);
            $ext0 = trim($v['B']);
            $mobile = trim($v['C']);

            $regtime = strtotime($this->excelTime(trim($v['G'])));
            $connect = D('Connect')->getConnectByOpenid('weixin', $openId);
            if (empty($connect)) {

            }else{
                D('Users')->save(array('user_id'=>$connect['uid'],'reg_time'=>$regtime));
            }
        }
    }
    //将读取到的excel数据导入数据库
    public function save_import($data)
    {
        header("Content-Type:text/html;charset=utf-8");

        foreach ($data as $k => $v) {
            if($k == 1){
                continue;
            }
            $nickname = trim($v['B']);

            $openId = trim($v['A']);
            $ext0 = trim($v['B']);
            $mobile = trim($v['C']);

            $regtime = strtotime($this->excelTime(trim($v['L'])));

            $data = array(
                'type' => 'weixin',
                'open_id' => $openId,
                'nickname' => $nickname
            );
            $connect = D('Connect')->getConnectByOpenid('weixin', $openId);
            if (empty($connect)) {
                $connect = $data;
                $connect['connect_id'] = D('Connect')->add($data);
            } else {
                D('Connect')->save(array('connect_id' => $connect['connect_id'],'nickname' => $nickname));
            }

            $user_data = array(
                'account' => $mobile,
                'password' => md5('123456'),
                'pay_password'=> md5('123456'),
                'nickname' => $nickname,
                'ext0' => $ext0,
                'mobile' => $mobile,
                'reg_time' => $regtime,
                'reg_ip' => get_client_ip()
            );
            //注册用户资料
            $user = D('Users')->where(array('mobile'=>$mobile))->find();
            if(!empty($user)){
                $user_data['user_id'] = $user['user_id'];
                D('Users')->save($user_data);
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $connect['uid']));
                continue;
            }else{
                $user_id = D('Users')->add($user_data);
                $user1 = D('Users')->find($user_id);
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $user_id));
            }
        }
    }
    //将读取到的excel数据导入数据库
    public function save_import2($data)
    {
        header("Content-Type:text/html;charset=utf-8");
        $res = array();

        foreach ($data as $k => $v) {
            if($k == 1){
                continue;
            }
            $nickname = trim($v['A']);
            $ext0 = trim($v['B']);
            $mobile = trim($v['C']);
            $openId = trim($v['D']);
            $regtime = strtotime($this->excelTime(trim($v['G'])));
            $data = array(
                'type' => 'weixin',
                'open_id' => $openId,
                'nickname' => $nickname
            );
            $connect = D('Connect')->getConnectByOpenid('weixin', $openId);
            if (empty($connect)) {
                $connect = $data;
                $connect['connect_id'] = D('Connect')->add($data);
            } else {
                D('Connect')->save(array('connect_id' => $connect['connect_id'],'nickname' => $nickname));
            }
            if(empty($connect['uid'])){
                if(empty($mobile)){
                    $account = uniqid() . '@' .'ymj.com';
                }else{
                    $account = $mobile;
                }
                $user_data = array(
                    'account' => $account,
                    'password' => md5('123456'),
                    'pay_password'=> md5('123456'),
                    'nickname' => $nickname,
                    'ext0' => $ext0,
                    'mobile' => $mobile,
                    'reg_time' => $regtime,
                    'reg_ip' => get_client_ip()
                );
                $user_id = D('Users')->add($user_data);
                D('Connect')->save(array('connect_id' => $connect['connect_id'], 'uid' => $user_id));
            }else{
                $user_data = array(
                    'user_id'=>$connect['uid'],
                    'nickname' => $nickname
                );
                D('Users')->save($user_data);
            }
        }
    }
//将读取到的excel数据导入数据库
    public function save_import3($data)
    {
        header("Content-Type:text/html;charset=utf-8");

        foreach ($data as $k => $v) {
            if($k == 1){
                continue;
            }
            $id = trim($v['A']);
            $nickname = trim($v['B']);
            $ext0 = trim($v['C']);
            $mobile = trim($v['D']);
            $openId = trim($v['F']);
            $tui = trim($v['G']);

            $connect = D('Connect')->getConnectByOpenid('weixin', $openId);
            $user = D('Users')->find($connect['uid']);

            $fuid1 = 0;
            $fuid2 = 0;
            $fuid3 = 0;
            $result1 = $this->getTopenid($data,$tui);
            if(!empty($result1)){
                $connect1 = D('Connect')->getConnectByOpenid('weixin', $result1['toid']);
                $result2 = $this->getTopenid($data,$result1['tt']);
                if(!empty($result2)){
                    $connect2 = D('Connect')->getConnectByOpenid('weixin', $result2['toid']);
                    D('Users')->save(array('user_id'=>$connect['uid'],'fuid1'=>$connect1['uid'],'fuid2'=>$connect2['uid']));
                }else{
                    D('Users')->save(array('user_id'=>$connect['uid'],'fuid1'=>$connect1['uid']));
                }
                continue;
            }
        }
    }
    public function getTopenid($data,$tui)
    {
        $result = array();
        if (!empty($tui)) {
            if ($tui != '总店') {
                $tuis = explode(']', $tui);
                $tuis[0] = str_replace('[', '', $tuis[0]);
                foreach ($data as $k => $v) {
                    $id = trim($v['A']);
                    $nickname = trim($v['B']);
                    if ($id == $tuis[0] || $nickname == $tuis[1]) {
                        $topenid = trim($v['F']);
                        $result['toid']=$topenid;
                        $tui = trim($v['G']);
                        $result['tt']=$tui;
                        return $result;
                        break;
                    }
                }
            }
        }
    }
    function excelTime($date, $time = false) {
        //如果是数字则转化，如果是有 - 或者 /，视作文本格式不作处理
        $type1 = strpos($date, '/');
        $type2 = strpos($date, '-');
        if($type1 || $type2){
            $return_date = $date;
        }else{
            $return_date=date('Y-m-d H:i:s',PHPExcel_Shared_Date::ExcelToPHP($date));
        }

        return $return_date;
    }
}
