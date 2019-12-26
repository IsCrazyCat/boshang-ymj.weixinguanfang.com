<?php
class AllAction extends CommonAction {
    public function index(){
        $config = D('Setting')->fetchAll();
        import('ORG.Util.Page');
		$where_activity = array('audit'=>1,'closed' =>0,'city_id'=>$this->city_id,'end_date'=>array('EGT',TODAY));
		$where_appoint = array('audit'=>1,'closed' => 0,'city_id'=>$this->city_id);
		$where_article = array('audit'=>1,'closed' => 0,'city_id'=>$this->city_id);
		$where_booking = array('audit'=>1,'closed' => 0,'city_id'=>$this->city_id);
		$where_xiaoqu = array('audit'=>1,'closed' => 0,'city_id'=>$this->city_id);
		$where_coupon = array('audit' => 1,'closed' => 0,'city_id'=>$this->city_id, 'expire_date' => array('EGT', TODAY));
		$where_crowd = array('audit'=>1,'closed' => 0, 'end_date' => array('EGT', TODAY));
		$where_ele = array('audit'=>1,'city_id'=>$this->city_id,'closed' => 0);
		$where_farm = array('audit'=>1,'closed' => 0,'city_id'=>$this->city_id);
		$where_goods = array('closed' => 0, 'audit' => 1,'city_id'=>$this->city_id, 'end_date' => array('EGT', TODAY));
		$where_hotel = array('audit'=>1,'closed' => 0,'city_id'=>$this->city_id);
		$where_life = array('audit' => 1,'city_id'=>$this->city_id);
		$where_biz = array('status'=>0,'city_id'=>$this->city_id);//暂时没有审核的已可以吧哎
		$where_shop = array('closed'=>0,'audit' =>1,'city_id'=>$this->city_id);
		$where_thread = array('closed' => 0,'city_id'=>$this->city_id);
		$where_post = array('audit'=>1,'closed' => 0,'city_id'=>$this->city_id);
		$where_tuan = array('closed'=>0,'audit' =>1,'city_id'=>$this->city_id,'end_date'=>array('EGT',TODAY));
		$where_village = array('closed' => 0,'city_id'=>$this->city_id);

        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $where_activity['title|intro'] = array('LIKE', '%' . $keyword . '%');
			$where_appoint['title'] = array('LIKE', '%' . $keyword . '%');
			$where_article['title|keywords'] = array('LIKE', '%' . $keyword . '%');//新闻
			$where_booking['shop_name|addr'] = array('LIKE', '%' . $keyword . '%');
			$where_xiaoqu['name|property'] = array('LIKE', '%' . $keyword . '%');//小区
			$where_coupon['title'] = array('LIKE', '%' . $keyword . '%');
			$where_crowd['title|intro'] = array('LIKE', '%' . $keyword . '%');
			$where_ele['product_name'] = array('LIKE', '%' . $keyword . '%');
			$where_farm['farm_name|intro|tel|addr'] = array('LIKE', '%' . $keyword . '%');
			$where_goods['title'] = array('LIKE', '%' . $keyword . '%');
			$where_hotel['hotel_name|tel|addr'] = array('LIKE', '%' . $keyword . '%');
			$where_life['qq|mobile|contact|title|num1|num2'] = array('LIKE', '%' . $keyword . '%');
			$where_biz['name|address'] = array('LIKE', '%' . $keyword . '%');//黄页
            $where_shop['shop_name|tags'] = array('LIKE','%'.$keyword.'%');
			$where_thread['thread_name'] = array('LIKE', '%' . $keyword . '%');
			$where_post['title'] = array('LIKE', '%' . $keyword . '%');
			$where_tuan['title'] = array('LIKE', '%' . $keyword . '%');
			$where_village['name|addr'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }

		$Activity=D('Activity');
		$Appoint = D('Appoint');
		$Article = D('Article');
		$Booking = D('Booking');
		$Xiaoqu = D('Community');
		$Coupon = D('Coupon');
		$Crowd = D('Crowd');
		$Ele=D('Eleproduct');
		$Farm = D('Farm');
		$Goods=D('Goods');
		$Hotel = D('Hotel');
		$Life = D('Life');
		$Biz = D('Biz');
		$Shop=D('Shop');
		$Thread = D('Thread');
		$Post = D('Threadpost');
		$Tuan=D('Tuan');
		$Village = D('Village');
		
		$list_activity=$Activity->Field('"活动" as t_name,"activity/detail" as t_url,"activity_id" as t_param,activity_id as t_id,title as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_activity)->select();
		$list_appoint=$Appoint->Field('"家政" as t_name,"appoint/detail" as t_url,"appoint_id" as t_param,appoint_id as t_id,title as t_title,photo as t_photo,concat("￥",round(price/100,2)) as t_note')->order($orderby)->where($where_appoint)->select();	
		$list_article=$Article->Field('"新闻" as t_name,"news/detail" as t_url,"article_id" as t_param,article_id as t_id,title as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_article)->select();	
		$list_booking=$Booking->Field('"订座" as t_name,"booking/detail" as t_url,"shop_id" as t_param,shop_id as t_id,shop_name as t_title,photo as t_photo,concat("￥",round(price/100,2)) as t_note')->order($orderby)->where($where_booking)->select();
		$list_xiaoqu=$Xiaoqu->Field('"小区" as t_name,"community/detail" as t_url,"community_id" as t_param,community_id as t_id,name as t_title,pic as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_xiaoqu)->select();
		$list_coupon=$Coupon->Field('"优惠券" as t_name,"coupon/detail" as t_url,"coupon_id" as t_param,coupon_id as t_id,title as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_coupon)->select();
		$list_crowd=$Crowd->Field('"众筹" as t_name,"crowd/detail" as t_url,"goods_id" as t_param,goods_id as t_id,title as t_title,photo as t_photo,concat("￥",round(all_price/100,2)) as t_note')->order($orderby)->where($where_crowd)->select();
		$list_ele=$Ele->Field('"外卖" as t_name,"ele/shop" as t_url,"shop_id" as t_param,shop_id as t_id,product_name as t_title,photo as t_photo,concat("￥",round(settlement_price/100,2)) as t_note')->order($orderby)->where($where_ele)->select();
		$list_farm=$Farm->Field('"农家乐" as t_name,"farm/detail" as t_url,"farm_id" as t_param,farm_id as t_id,farm_name as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_farm)->select();

		$list_goods=$Goods->Field('"商城" as t_name,"mall/detail" as t_url,"goods_id" as t_param,goods_id as t_id,title as t_title,photo as t_photo,concat("￥",round(mall_price/100,2)) as t_note')->order($orderby)->where($where_goods)->select();
		$list_hotel=$Hotel->Field('"酒店" as t_name,"hotels/detail" as t_url,"hotel_id" as t_param,hotel_id as t_id,hotel_name as t_title,photo as t_photo,concat("￥",round(price/100,2)) as t_note')->order($orderby)->where($where_hotel)->select();
		$list_life=$Life->Field('"分类" as t_name,"life/detail" as t_url,"life_id" as t_param,life_id as t_id,title as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_life)->select();
		$list_biz=$Biz->Field('"黄页" as t_name,"biz/detail" as t_url,"pois_id" as t_param,pois_id as t_id,name as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_biz)->select();
		$list_shop =$Shop->Field('"商家" as t_name,"shop/detail" as t_url,"shop_id" as t_param,shop_id as t_id,shop_name as t_title,photo as t_photo,tel as t_note')->order($orderby)->where($where_shop)->select();
		$list_thread=$Thread->Field('"贴吧" as t_name,"thread/detail" as t_url,"thread_id" as t_param,thread_id as t_id,thread_name as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_thread)->select();
		$list_post=$Post->Field('"帖子" as t_name,"thread/postdetail" as t_url,"post_id" as t_param,post_id as t_id,title as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_post)->select();
		$list_tuan=$Tuan->Field('"套餐" as t_name,"tuan/detail" as t_url,"tuan_id" as t_param,tuan_id as t_id,title as t_title,photo as t_photo,concat("￥",round(tuan_price/100,2)) as t_note')->order($orderby)->where($where_tuan)->select();
		$list_village=$Village->Field('"乡村" as t_name,"village/detail" as t_url,"village_id" as t_param,village_id as t_id,name as t_title,photo as t_photo,FROM_UNIXTIME(create_time,"%Y-%m-%d") as t_note')->order($orderby)->where($where_village)->select();
	
		$list = array();
		if (!empty($config['operation']['huodong'])){
			if(!empty($list_activity)){
				$list=array_merge($list,$list_activity);
			}
		}
		if (!empty($config['operation']['appoint'])){
			if(!empty($list_appoint)){
				$list=array_merge($list,$list_appoint);
			}
		}
		if (!empty($config['operation']['news'])){
			if(!empty($list_article)){
				$list=array_merge($list,$list_article);
			}
		}
		if (!empty($config['operation']['booking'])){
			if(!empty($list_booking)){
				$list=array_merge($list,$list_booking);
			}
		}
		if(!empty($list_xiaoqu)){
			$list=array_merge($list,$list_xiaoqu);
		}
		if(!empty($list_coupon)){
			$list=array_merge($list,$list_coupon);
		}
		if (!empty($config['operation']['crowd'])){
			if(!empty($list_crowd)){
				$list=array_merge($list,$list_crowd);
			}
		}
		if(!empty($list_ele)){
			$list=array_merge($list,$list_ele);
		}
		if (!empty($config['operation']['farm'])){
			if(!empty($list_farm)){
				$list=array_merge($list,$list_farm);
			}
		}
		if (!empty($config['operation']['mall'])){
			if(!empty($list_goods)){
				$list=array_merge($list,$list_goods);
			}
		}
		if (!empty($config['operation']['hotels'])){
			if(!empty($list_hotel)){
				$list=array_merge($list,$list_hotel);
			}
		}
		if (!empty($config['operation']['life'])){
			if(!empty($list_life)){
				$list=array_merge($list,$list_life);
			}
		}
		if(!empty($list_biz)){
			$list=array_merge($list,$list_biz);
		}
		if(!empty($list_shop)){
			$list=array_merge($list,$list_shop);
		}
		if (!empty($config['operation']['thread'])){
			if(!empty($list_thread)){
				$list=array_merge($list,$list_thread);
			}
		}
		if (!empty($config['operation']['thread'])){
			if(!empty($list_post)){
				$list=array_merge($list,$list_post);
			}
		}
		if(!empty($list_tuan)){
			$list=array_merge($list,$list_tuan);
		}
		if (!empty($config['operation']['village'])){
			if(!empty($list_village)){
				$list=array_merge($list,$list_village);
			}
		}
		
		$Page=new Page(count($list),10);
		$list=array_slice($list,$Page->firstRow,$Page->listRows);
		$show=$Page->show();
		$this->assign('searchindex',0);
        $this->assign('total_num',count($list));
        $this->assign('list',$list);

        $this->assign('page', $show);
        $this->display();
    }
}