<?php



class  TagLibCalldata extends TagLib{
    
    protected  $tags = array(
        'calldata'=> array(
            'attr'=> 'mdl,where,limit,order,debug,cache,format','close'=>1
        ),
        'callfunc'=>array(
            'attr' => 'mdl,func,debug','close' => 1
        ),
		 'location'=>array( //汇e加添加自定义标签开始。
            'attr' => 'cid'
        ),
		 'locationtuan'=>array(
            'attr' => 'cid'
        )//结束+++++++++++++++++++++++
		
    );
    
    
    public function _callfunc($attr,$content){
        $attr = $this->parseXmlAttr($attr);
        $attr['mdl'] = empty($attr['mdl']) ? '' : $attr['mdl'];
        $attr['func'] = empty($attr['func']) ? '' : $attr['func'];
        $attr['debug'] = $attr['debug'] ? true : false;
        if(empty($attr['mdl']) || empty($attr['func'])){
            return  '';
        }
        $str = '<?php  ';
        
         $str.=' $items = D("'.$attr['mdl'].'")->'.$attr['func'].'(); ';
  
         if($attr['debug']){           
            $str.=' print_r(D("'.$attr['mdl'].'")->getLastSql()); ';
        }
        
        $str.= ' $index=0; foreach($items  as $item): $index++; ?>';
        $str.=$content;
        $str.=' <?php endforeach; ?>';
        return $str;
    }


    public function _calldata($attr,$content){

        $attr = $this->parseXmlAttr($attr);
        
        $attr['mdl'] = empty($attr['mdl']) ? 'Recommend' : $attr['mdl'];
        $attr['where'] = empty($attr['where']) ? '' : $this->parseCondition($attr['where']);
       
        $attr['order'] = empty($attr['order']) ? '' : $attr['order'];
        $attr['limit'] = empty($attr['limit']) ? '0,10' : $attr['limit'];
        $attr['cache'] = $attr['cache'] ? (int)$attr['cache'] : 0;
        $attr['format'] = $attr['format'] ? true : false;
        $attr['debug'] = $attr['debug'] ? true : false;
         $token = join(',',$attr );
        $str = '<?php  ';
        if($attr['cache']){
            $str.='
                $cache = cache(array(\'type\'=>\'File\',\'expire\'=> '.$attr['cache'].'));
                $token = md5("'.$token.'");   
                if(!$items= $cache->get($token)){ ';
        }
        $str.=' $items = D("'.$attr['mdl'].'")->where("'.$attr['where'].'")->order("'.$attr['order'].'")->limit("'.$attr['limit'].'")->select(); ';
        if($attr['debug']){
            
            $str.=' print_r(D("'.$attr['mdl'].'")->getLastSql()); ';
        }
        
        if($attr['format']){
            
            $str.='
                
                    $items = D("'.$attr['mdl'].'")->CallDataForMat($items);
                
                ';
            
        }
        
        
        if($attr['cache']){
            
            $str.='  
                $cache->set($token,$items);
              }      ;
            ';
        }
        $str.= ' $index=0; foreach($items  as $item): $index++; ?>';
        $str.=$content;
        $str.=' <?php endforeach; ?>';
        return $str;
    }
	//=====================//汇e加添加自定义标签开始。========
		Public function _location ($attr, $content) {
		$attr = $this->parseXmlAttr($attr);
		$cid = $attr['cid'];
		$str = <<<str
		<?php
		\$cid = {$cid};
		if (S('location_' . \$cid)) {
		\$_location_result = S('location_' . \$cid);
		} else {
		\$_location_category = M('goods_cate')->select();
		\$_location_result = array_reverse(get_all_parent(\$_location_category, \$cid));
		S('location_' . \$cid);
	}
	foreach (\$_location_result as \$v) :
		extract(\$v);
			
?>
str;
		$str .= $content;
		$str .= '<?php endforeach;?>';
		return $str;
	}
 	//================================================================
    
    Public function _locationtuan ($attr, $content) {
		$attr = $this->parseXmlAttr($attr);
		$cid = $attr['cid'];
		$str = <<<str
		<?php
		\$cid = {$cid};
		if (S('location_' . \$cid)) {
		\$_locationtuan_result = S('location_' . \$cid);
		} else {
		\$_locationtuan_category = M('tuan_cate')->select();
		\$_locationtuan_result = array_reverse(get_all_parent(\$_locationtuan_category, \$cid));
		S('location_' . \$cid);
	}
	foreach (\$_locationtuan_result as \$v) :
		extract(\$v);
			
?>
str;
		$str .= $content;
		$str .= '<?php endforeach;?>';
		return $str;
	}
    
 	//=======================结束=========================================


    
    

    
}