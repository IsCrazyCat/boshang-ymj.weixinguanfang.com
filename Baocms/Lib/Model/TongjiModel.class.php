<?php



class  TongjiModel extends CommonModel{
    protected $pk   = 'tongji_id';
    protected $tableName =  'tongji';
    
    private $type = array(
        1   => '套餐',
        2   => '购物',
        3   => '外卖',
    );
    
    private $from = array(
        'tui'       => '推广',
        'baidu'     => '百度',
        'google'    => '谷歌',
        'soso'      => 'soso',
        '360'       => '360',
        'sogou'     => '搜狗',//其他的搜索引擎忽略了
    );
    
    public function laiyuan($bg_date,$end_date){
        $bg_date = addslashes($bg_date);
        $end_date = addslashes($end_date);
        $datas = $this->query("select  count(1) as num,`from` from ".$this->getTableName()." where `from`!='tui' and `date`>='".$bg_date."' AND `date` <='".$end_date."'  group by  `from` ");
        $a1  = $a2= $a3= array();
        foreach($datas as $val){
            $a1[] = '["'.$this->from[$val['from']].'",'.$val['num'].']';
            $a2[] = '"'.$this->from[$val['from']].'"';
            $a3[] = $val['num'];
        }
        $data = array(
            'laiyuan1'  =>join(',',$a1),
            'laiyuan2'  => array(
                'from' => join(',',$a2),
                'num'  => join(',',$a3)
            )
        );
        return $data ;
    }
    
    
     public function lmoney($bg_date,$end_date){
        $bg_date = addslashes($bg_date);
        $end_date = addslashes($end_date);
        $datas = $this->query("select  sum(money) as num,`from` from ".$this->getTableName()." where `from`!='tui' and `date`>='".$bg_date."' AND `date` <='".$end_date."'  group by  `from` ");
        $a1  = $a2= $a3= array();
        foreach($datas as $val){
            $a1[] = '["'.$this->from[$val['from']].'",'.round($val['num']/100,2).']';
            $a2[] = '"'.$this->from[$val['from']].'"';
            $a3[] = round($val['num']/100,2);
        }
        $data = array(
            'laiyuan1'  =>join(',',$a1),
            'laiyuan2'  => array(
                'from' => join(',',$a2),
                'num'  => join(',',$a3)
            )
        );
        return $data ;
    }
    
    public function tmoney($bg_date,$end_date){
        $bg_date = addslashes($bg_date);
        $end_date = addslashes($end_date);
        $datas = $this->query("select  sum(money) as num,`keyword` from ".$this->getTableName()." where `from`='tui' and `date`>='".$bg_date."' AND `date` <='".$end_date."'  group by  `keyword` ");
        $a = $a2= array();
        $tui= D('Tui')->fetchAll();
        foreach($datas as $val){
            $a[] = '"'.$tui[$val['keyword']].'"';
            $a2[] =round($val['num']/100,2);
        }
        return  array(
            'keyword' => join(',',$a),
            'num'     => join(',',$a2),  
        );
    }
    
    public function kmoney($bg_date,$end_date){
        $bg_date = addslashes($bg_date);
        $end_date = addslashes($end_date);
        $datas = $this->query("select  sum(money) as num,`keyword` from ".$this->getTableName()." where `from`!='tui' and `date`>='".$bg_date."' AND `date` <='".$end_date."'  group by  `keyword` limit 0,20");
        $a = $a2= array();
        $tui= D('Tui')->fetchAll();
        foreach($datas as $val){
            $a[] = '"'.$val['keyword'].'"';
            $a2[] =round($val['num']/100,2);
        }
        return  array(
            'keyword' => join(',',$a),
            'num'     => join(',',$a2),  
        );
    }
    
    public function keyword($bg_date,$end_date){
        $bg_date = addslashes($bg_date);
        $end_date = addslashes($end_date);
        $datas = $this->query("select  count(1) as num,`keyword` from ".$this->getTableName()." where `from`!='tui' and `date`>='".$bg_date."' AND `date` <='".$end_date."'  group by  `keyword` limit 0,20");
   
        $a = $a2= array();
        $tui= D('Tui')->fetchAll();
        foreach($datas as $val){
            $a[] = '"'.$val['keyword'].'"';
            $a2[] = $val['num'];
        }
        return  array(
            'keyword' => join(',',$a),
            'num'     => join(',',$a2),  
        );
    }
    
    
    public function tuiguan($bg_date,$end_date){
        $bg_date = addslashes($bg_date);
        $end_date = addslashes($end_date);
        $datas = $this->query("select  count(1) as num,`keyword` from ".$this->getTableName()." where `from`='tui' and `date`>='".$bg_date."' AND `date` <='".$end_date."'  group by  `keyword` ");
   
        $a = $a2= array();
        $tui= D('Tui')->fetchAll();
        foreach($datas as $val){
            $a[] = '"'.$tui[$val['keyword']].'"';
            $a2[] = $val['num'];
        }
        return  array(
            'keyword' => join(',',$a),
            'num'     => join(',',$a2),  
        );
    }
    
    
    public function log($type,$money=0){ 
         $array = array(
            'type'      => (int)$type,
            'money'     => (int)$money,
            'year'      =>date('Y',NOW_TIME),
            'month'     => date('m',NOW_TIME),
            'day'       => date('d',NOW_TIME), 
            'date'      => TODAY,
            'is_mobile' => is_mobile() ? 1 : 0,
            'is_weixin' => is_weixin() ? 1 : 0,
        );
        $tui = cookie('tui_from');
        if(!empty($tui)){
            $array['from'] = 'tui';
            $array['keyword'] = htmlspecialchars($tui);
        }
        $this->add($array);
        $from = cookie('search_word_from');
        $from = json_decode($from,true);
        if(empty($from['from']) || empty($from['keyword']) || !isset($this->from[$from['from']])) return false;
        $array['from'  ]  = $from['from'];
        $array['keyword']   = htmlspecialchars($from['keyword']);
       
        return $this->add($array);
    }
    
    
    
    
}