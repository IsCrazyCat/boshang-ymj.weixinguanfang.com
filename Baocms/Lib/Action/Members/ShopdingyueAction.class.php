<?php
/*
 * 软件为合肥生活宝网络公司出品，未经授权许可不得使用！
 * 作者：baocms团队
 * 官网：www.baocms.com
 * 邮件: youge@baocms.com  QQ 800026911
 */
class ShopdingyueAction extends CommonAction {
    //设置
     public function base() {
        if ($this->isPost()) {

            $data['uid'] = (int) $this->uid;
            //查找商家id
            $objs=M('shop');
            $map['user_id']=$data['uid'];
            $data['shop_id']=$objs->where($map)->getField('shop_id');
            if (empty($data['shop_id'])) {
               $data['shop_id']=0;
            }
            $city_id = (int) $this->_post('city_id');
            $area_id = (int) $this->_post('area_id');
            $business_id = (int) $this->_post('business_id');
            $channel_id = (int) $this->_post('channel_id');   
            $cate_id = (int) $this->_post('cate_id');  
            $data['sms'] = (int) $this->_post('sms');
            // $attr_id = (int) $this->_post('attr_id');
            $chosenIdsStr = $_POST['chosenIds'];
            if(!empty($chosenIdsStr)){
                $chosenIds = explode(",", $chosenIdsStr);
                $saveIds = array();
                foreach( $chosenIds as $idStr)
                {
                    $tArray = explode("|", $idStr);
                    foreach( $tArray as $t)
                    {
                        if(!in_array($t, $saveIds)){
                            array_push($saveIds, $t);
                            $tempArray = explode("-", $t);
                            $key = 'select'.$tempArray[0];
                            if(empty($data[$key])){
                                $data[$key] = $tempArray[1];
                            }else{
                                $data[$key] = $data[$key].",".$tempArray[1];
                            }
                        }
                    }
                    
                }
            }
            // $se1=0;
            // foreach( $_POST['select1'] as $i1)
            // {
            //  if ($se1==0) {
            //       $data['select1'] = $data['select1']. $i1;
            //  }else{
            //      $data['select1'] = $data['select1'].",". $i1;
            //  }
            //  $se1=$se1+1;
            // }

            // $se2=0;
            // foreach( $_POST['select2'] as $i2)
            // {
            //      if ($se2==0) {
            //           $data['select2'] = $data['select2']. $i2;
            //      }else{
            //          $data['select2'] = $data['select2'].",". $i2;
            //      }
            //      $se2=$se2+1;
            // }

            //  $se3=0;
            // foreach( $_POST['select3'] as $i3)
            // {
            //     if ($se3==0) {
            //           $data['select3'] = $data['select3']. $i3;
            //      }else{
            //          $data['select3'] = $data['select3'].",". $i3;
            //      }
            //      $se3=$se3+1;
            // }

            //  $se4=0;
            // foreach( $_POST['select4'] as $i4)
            // {
            //      if ($se4==0) {
            //           $data['select4'] = $data['select4']. $i4;
            //      }else{
            //          $data['select4'] = $data['select4'].",". $i4;
            //      }
            //      $se4=$se4+1;
            // }

            //  $se5=0;
            // foreach( $_POST['select5'] as $i5)
            // {
            //     if ($se5==0) {
            //           $data['select5'] = $data['select5']. $i5;
            //      }else{
            //          $data['select5'] = $data['select5'].",". $i5;
            //      }
            //      $se5=$se5+1;
            // }

            //组成数组          
            $data['sitelist'] = $city_id.','.$area_id.','.$business_id;    
            $data['catlist']=$channel_id.','.$cate_id;
            $data['status']=1;           
            $data['create_time']=time();
            $data['sms_number']=1;
            //查找限制几条
            $number=M('shop_dingyue_set')->getField('count_number');
            $map['uid']=(int) $this->uid;
            $map['status']=1;
            $dingyuecount=M('shop_dingyue')->where($map)->count();
            $number=(int)$number;
            if ($dingyuecount<$number) {
               $data['audit']=1;
               $result=M('shop_dingyue')->add($data);
                if (isset($result) ) {
                    $this->baoSuccess('订阅成功！', U('shopdingyue/base'));
                }
                $this->baoError('订阅失败！');
            }else{
            	$data['audit']=0;
            	$result=M('shop_dingyue')->add($data);
            	if (isset($result) ) {
            		$this->baoSuccess('超过订阅限制，需付费开通，请与网站管理人员联系。', U('shopdingyue/base'));
            	}
            }
           
        } else {             
            //list
            $map['status']=1;
            $map['uid']=(int) $this->uid;
//             var_dump($map);
            $list=M('shop_dingyue')->where($map)->select();
            $slist=array();
            foreach ($list as $a =>$val) {              
                 $sitelist=explode(",",$val['sitelist']);
                 foreach ($sitelist as $k => $v) {
                    switch ($k) {
                        case 0:
                            $model = M('city');
                            $map['city_id']=$v;                                      
                            //得到答案的id
                            $slist[$k] = $model->where($map)->getField('name');
                            break;
                        case 1:
                            $model = M('area');
                            $map['area_id']=$v;                                      
                            //得到答案的id
                            $slist[$k] = $model->where($map)->getField('area_name');
                            break;
                        case 2:
                            $model = M('business');
                            $map['business_id']=$v;                                      
                            //得到答案的id
                            $slist[$k] = $model->where($map)->getField('business_name');
                            break;                        
                        default:
                            # code...
                            break;
                    }
                 }
                 $catlist=explode(",",$val['catlist']);
                 $clist=array();
                 $chl = D('Lifecate')->getChannelMeans();
//                  var_dump($chl);exit;
                 foreach ($catlist as $k1 => $v1) {
                 	switch ($k1) {
                 		 case 0:
                 		 	$clist[$k1] = $chl[$v1];
                 		 	break;
                 		 case 1:
                 		 	$model = M('life_cate');
		                    $map['cate_id']=$v1;                                      
		                    //得到答案的id
		                    $clist[$k1]=$model->where($map)->getField('cate_name');
                 			break;
                         case 2:
                            $model = M('life_cate_attr');
                            $map['attr_id']=$v1;                                      
                            //得到答案的id
                            $clist[$k1]=$model->where($map)->getField('attr_name');
                            break;
                 	}
                   
                 }   
                 $list[$a]['sitelist']=$slist;
                 $list[$a]['catlist']=$clist;  


                 //显示分类select1
                 $select1=explode(",",$val['select1']); 
				 $kslist1=array();				
                 foreach ($select1 as $ks1 => $vs1) {
                    $modelks=M('life_cate_attr');
                    $mapks['attr_id']=$vs1;
                    $kslist1[$ks1]=$modelks->where($mapks)->getField('attr_name');
                 }

                 //显示分类select2
                 $select2=explode(",",$val['select2']);
				 $kslist2=array(); 
                 foreach ($select2 as $ks2 => $vs2) {
                    $modelks2=M('life_cate_attr');
                    $mapks2['attr_id']=$vs2;
                    $kslist2[$ks2]=$modelks2->where($mapks2)->getField('attr_name');
                 }                

                 //显示分类select3
                 $select3=explode(",",$val['select3']);
                 $kslist3=array();
                 foreach ($select3 as $ks3 => $vs3) {
                    $modelks3=M('life_cate_attr');
                    $mapks3['attr_id']=$vs3;
                    $kslist3[$ks3]=$modelks3->where($mapks3)->getField('attr_name');
                 }

                 //显示分类select4
                 $select4=explode(",",$val['select4']); 
                $kslist4=array();
                 foreach ($select4 as $ks4 => $vs4) {
                    $modelks4=M('life_cate_attr');
                    $mapks4['attr_id']=$vs4;
                    $kslist4[$ks4]=$modelks4->where($mapks4)->getField('attr_name');
                 }

                 //显示分类select5
                 $select5=explode(",",$val['select5']); 
                 $kslist5=array();
                 foreach ($select5 as $ks5 => $vs5) {
                    $modelks5=M('life_cate_attr');
                    $mapks5['attr_id']=$vs5;
                    $kslist5[$ks5]=$modelks5->where($mapks5)->getField('attr_name');
                 }                  
                $list[$a]['select1']=$kslist1;    
                $list[$a]['select2']=$kslist2;    
                $list[$a]['select3']=$kslist3;    
                $list[$a]['select4']=$kslist4;    
                $list[$a]['select5']=$kslist5;               
                
            }
            $chl = D('Lifecate')->getChannelMeans();
            $this->assign('list', $list);
            $this->assign('channelmeans', $chl);

            $model = M('life_cate');
            $cate=$model->select();
            $this->assign('cate', $cate);
            $this->assign('channel', $cate['channel_id']);
            //查找地区限制
            $city_id=D('shop_dingyue_set')->getField('city_id');
            $area_id=D('shop_dingyue_set')->getField('area_id');
            $business_id=D('shop_dingyue_set')->getField('business_id');
            if ($business_id) {
            	//全有            	
            }elseif ($area_id){
            	//三级隐藏
            	$this->assign('hideb', 'hidebusiness_id');
            }else {
            	//三级二级隐藏
            	$this->assign('hideb', 'hidebusiness_id');
            	$this->assign('hidea', 'hidearea_id');
            }
            
			//分类限制
            $one_cate=D('shop_dingyue_set')->getField('one_cate');
            $two_cate=D('shop_dingyue_set')->getField('two_cate');
            if ($two_cate) {
            	//全有
            }else{
            	//隐藏二级
            	$this->assign('hidet', 'hidetwocate_id');
            }

            $this->assign('biz', $biz);
            $this->display();
        }
    }
    public  function  index(){
	    	$map = array();
	    	if($cate_id = $this->_param('cate_id')){              
                $map['catlist'] = array('LIKE','%,'.$cate_id);                
            }
	    	if($audit = $this->_param('audit')){
	    		       $map['audit'] = $audit;	    		
	    	}
            if($select1 = $_POST['select1']){
               $se1=0;
               foreach( $select1 as $i1)
                {
                    if ($se1==0) {
                      $map1['select1'] = $map1['select1']. $i1;
                    }else{
                      $map1['select1'] = $map1['select1'].",". $i1;
                    }
                    $se1=$se1+1;
                }
                $map['select1'] =array('LIKE','%'.$map1['select1'].'%'); 
            }

            if($select2 = $_POST['select2']){
               $se2=0;
               foreach( $select2 as $i2)
                {
                    if ($se2==0) {
                      $map2['select2'] = $map1['select2']. $i2;
                    }else{
                      $map2['select2'] = $map1['select2'].",". $i2;
                    }
                    $se2=$se2+1;
                }
                $map['select2'] =array('LIKE','%'.$map2['select2'].'%'); 
            }

            if($select3 = $_POST['select3']){
               $se3=0;
               foreach( $select3 as $i3)
                {
                    if ($se3==0) {
                      $map3['select3'] = $map3['select3']. $i3;
                    }else{
                      $map3['select3'] = $map3['select3'].",". $i3;
                    }
                    $se3=$se3+1;
                }
                $map['select3'] =array('LIKE','%'.$map3['select3'].'%'); 
            }

            if($select4 = $_POST['select4']){
               $se4=0;
               foreach( $select4 as $i4)
                {
                    if ($se4==0) {
                      $map4['select4'] = $map4['select4']. $i4;
                    }else{
                      $map4['select4'] = $map4['select4'].",". $i4;
                    }
                    $se4=$se4+1;
                }
                $map['select4'] =array('LIKE','%'.$map4['select4'].'%'); 
            }

            if($select5 = $_POST['select5']){
               $se5=0;
               foreach( $select5 as $i5)
                {
                    if ($se5==0) {
                      $map5['select5'] = $map5['select5']. $i5;
                    }else{
                      $map5['select5'] = $map5['select5'].",". $i5;
                    }
                    $se5=$se5+1;
                }
                $map['select5'] =array('LIKE','%'.$map5['select5'].'%'); 
            }      
            
    		$map['status']=1;
    		$map['uid']=(int) $this->uid;
    		$list=D('shop_dingyue')->where($map)->select();
            $slist=array();
            foreach ($list as $a =>$val) {                
                 $sitelist=explode(",",$val['sitelist']);
                 foreach ($sitelist as $k => $v) {
                    switch ($k) {
                        case 0:
                            $model = M('city');
                            $map['city_id']=$v;                                      
                            //得到答案的id
                            $slist[$k] = $model->where($map)->getField('name');
                            break;
                        case 1:
                            $model = M('area');
                            $map['area_id']=$v;                                      
                            //得到答案的id
                            $slist[$k] = $model->where($map)->getField('area_name');
                            break;
                        case 2:
                            $model = M('business');
                            $map['business_id']=$v;                                      
                            //得到答案的id
                            $slist[$k] = $model->where($map)->getField('business_name');
                            break;                        
                        default:
                            # code...
                            break;
                    }
                 }
                 $chl = D('Lifecate')->getChannelMeans();
                 $catlist=explode(",",$val['catlist']);
                 $clist=array();
                 // var_dump($catlist);exit;
                 foreach ($catlist as $k1 => $v1) {
                 	switch ($k1) {
                 		 case 0:
                 		 	$clist[$k1] = $chl[$v1];                 		 	 
                 		 	break;
                 		 case 1:
                 		 	$model = M('life_cate');
		                    $map['cate_id']=$v1;                                      
		                    //得到答案的id
		                    $clist[$k1]=$model->where($map)->getField('cate_name');
                 			break;
                        case 2:
                            $model = M('life_cate_attr');
                            $map['attr_id']=$v1;                                      
                            //得到答案的id
                            $clist[$k1]=$model->where($map)->getField('attr_name');
                            break;
                 	}                   
                 }   
                 $list[$a]['sitelist']=$slist;
                 $list[$a]['catlist']=$clist;  

                  //显示分类select1
                 $select1=explode(",",$val['select1']); 
                  $kslist1=array();
                 foreach ($select1 as $ks1 => $vs1) {
                    $modelks=M('life_cate_attr');
                    $mapks['attr_id']=$vs1;
                    $kslist1[$ks1]=$modelks->where($mapks)->getField('attr_name');
                 }

                 //显示分类select2
                 $select2=explode(",",$val['select2']); 
                  $kslist2=array();
                 foreach ($select2 as $ks2 => $vs2) {
                    $modelks2=M('life_cate_attr');
                    $mapks2['attr_id']=$vs2;
                    $kslist2[$ks2]=$modelks2->where($mapks2)->getField('attr_name');
                 }                

                 //显示分类select3
                 $select3=explode(",",$val['select3']);
                  $kslist3=array();
                 foreach ($select3 as $ks3 => $vs3) {
                    $modelks3=M('life_cate_attr');
                    $mapks3['attr_id']=$vs3;
                    $kslist3[$ks3]=$modelks3->where($mapks3)->getField('attr_name');
                 }

                 //显示分类select4
                 $select4=explode(",",$val['select4']); 
                  $kslist4=array();
                 foreach ($select4 as $ks4 => $vs4) {
                    $modelks4=M('life_cate_attr');
                    $mapks4['attr_id']=$vs4;
                    $kslist4[$ks4]=$modelks4->where($mapks4)->getField('attr_name');
                 }

                 //显示分类select5
                 $select5=explode(",",$val['select5']); 
                  $kslist5=array();
                 foreach ($select5 as $ks5 => $vs5) {
                    $modelks5=M('life_cate_attr');
                    $mapks5['attr_id']=$vs5;
                    $kslist5[$ks5]=$modelks5->where($mapks5)->getField('attr_name');
                 }                  
                $list[$a]['select1']=$kslist1;    
                $list[$a]['select2']=$kslist2;    
                $list[$a]['select3']=$kslist3;    
                $list[$a]['select4']=$kslist4;    
                $list[$a]['select5']=$kslist5;             
            }
            $this->assign('chanel', $chl);
            $this->assign('list', $list);
            $this->display();
       
    }
    public  function del($dingyue_id = 0){
    	$dingyue_id = (int) $dingyue_id;
    	$obj = D('shop_dingyue');
    	$map['dingyue_id']=$dingyue_id;
    	$data['status']=-1;
    	$result=$obj->where($map)->save($data);
    	if ($result) {
    		echo"<script>alert('删除成功！');location.href=document.referrer;</script>";
    		 
    	}else{
    		echo"<script>alert('删除失败！');location.href=document.referrer;</script>";
    	}    	
    }
    public function catajax($channel_id=0){
    	$channel_id = (int) $channel_id;
    	$obj=M('life_cate');
    	
    	$map=array('channel_id'=> $channel_id);
    	$result = array();
    	$result=$obj->where($map)->field('cate_id,cate_name')->select();
    	echo  json_encode($result);
    	exit;
    }
    public function attrajax($cate_id=0){
        $cate_id = (int) $cate_id;
        $obj=M('life_cate_attr');
        
        $map=array('cate_id'=> $cate_id);
        //查找名称
        $objs=M('life_cate');  
        $result = array();
        // $result=$obj->where($map)->field('attr_id,attr_name')->select();
        $result['select']=$objs->where($map)->field('select1,select2,select3,select4,select5')->select();
        $result['attr']=$obj->where($map)->field('attr_id,attr_name,type')->select();
        echo  json_encode($result);
        exit;
    }
    public function sms_open($dingyue_id=0,$sms=0){
    	$dingyue_id=(int) $dingyue_id;
    	$sms = (int) $sms; 
		if ($sms ==1) {
			$data['sms']=-1;
			$smsname="关";
		}elseif ($sms==-1){
			$data['sms']=1;
			$smsname="开";
		}
    	$obj=M('shop_dingyue');    	 
    	$map=array('dingyue_id'=> $dingyue_id);
    	$result=$obj->where($map)->save($data);
    	if ($result) {
    		$response=array('sms'=>$smsname,
    						'status'=>1
    						);
    	}else{
    		$response=array('status'=>0);
    	}
    	
    	echo  json_encode($response);
    	exit;
    }
   
}
