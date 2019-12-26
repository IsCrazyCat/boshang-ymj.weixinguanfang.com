<?php


class  YuyueAction extends CommonAction{
 
    public function index(){
        $Shopyuyue = D('Shopyuyue');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('shop_id'=>  $this->shop_id);
        $count = $Shopyuyue->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Shopyuyue->where($map)->order(array('yuyue_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list  as $k=>$val){            
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;          
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $is_yuyue = $this->shop['yuyue_date'] < TODAY ? false : true;
        $this->assign('is_yuyue',$is_yuyue);
        $this->display(); // 输出模板           
    }
    
    public function used(){
        if($this->isPost()){
            
            $code = $this->_post('code', false);
            $res = array();
            foreach ($code as $k => $val) {
                if (!empty($val)) {
                    $res[$k] = $val;
                }
            }
            if (empty($res)) {
                $this->baoMsg('请输入电子预约券！');
            }
            $obj =  D('Shopyuyue');
            $return = array();
            $ip = get_client_ip();
            foreach($code  as $var){
                $var = (int) $var;
                if(!empty($var)){
                    $data =$obj->find(array('where'=>array('code'=>$var)));
                    if(!empty($data) && $data['shop_id'] == $this->shop_id && $data['used'] == 0){
                      $obj->save(array('yuyue_id'=>$data['yuyue_id'],'used'=>1,'used_time'=>NOW_TIME,'used_ip'=>$ip));
                      $return[$var] = $var;
                    }
                }
            }   
            if(empty($return)){
                $this->baoMsg('没有可消费的电子预约券！');
            }
            if(NOW_TIME - $this->shop['ranking'] < 86400){ //更新排名
                D('Shop')->save(array('shop_id'=>  $this->shop_id,'ranking'=>NOW_TIME));
            }
            $message = "恭喜您，您成功消费的电子预约券如下：" . join(',', $return);
            $this->baoOpen($message, true, "layui-layer-demo");
        }else{
            $this->display();
        }       
    }
    
}