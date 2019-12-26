<?php
class CloudAction extends CommonAction{
	protected function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['cloud'] == 0) {
				$this->error('此功能已关闭');die;
		}
     }

    public function index( ){
        $cloudlogs = D( "Cloudlogs" );
        $cloudgoods = D( "Cloudgoods" );
        import( "ORG.Util.Page" );
        $goods_ids = $cloudlogs->where( array("user_id" => $this->uid))->getField("goods_id",TRUE );
        array_unique( $goods_ids );
        $map = array( "closed" => 0, "audit" => 1 );
        $map['goods_id'] = array( "IN",$goods_ids);
        if ( ( $bg_date = $this->_param( "bg_date", "htmlspecialchars" ) ) && ( $end_date = $this->_param( "end_date", "htmlspecialchars" ) ) ){
            $bg_time = strtotime( $bg_date );
            $end_time = strtotime( $end_date );
            $map['create_time'] = array(array("ELT",$end_time),array("EGT",$bg_time));
            $this->assign( "bg_date", $bg_date );
            $this->assign( "end_date", $end_date );
        }
        else{
            if ( $bg_date = $this->_param( "bg_date", "htmlspecialchars" ) ){
                $bg_time = strtotime( $bg_date );
                $this->assign( "bg_date", $bg_date );
                $map['create_time'] = array( "EGT",$bg_time);
            }
            if ( $end_date = $this->_param( "end_date", "htmlspecialchars" ) ){
                $end_time = strtotime( $end_date );
                $this->assign( "end_date", $end_date );
                $map['create_time'] = array("ELT",$end_time);
            }
        }
        if ( $keyword = $this->_param( "keyword", "htmlspecialchars" ) ){
            $map['title|intro'] = array("LIKE","%".$keyword."%");
            $this->assign( "keyword", $keyword );
        }
        if ( isset( $_GET['st'] ) || isset( $_POST['st'] ) ){
            $st = (int)$this->_param( "st" );
            if ( $st != 999 ){
                $map['status'] = $st;
            }
            $this->assign( "st", $st );
        }
        else{
            $this->assign( "st", 999 );
        }
        $count = $cloudgoods->where( $map )->count( );
        $Page = new Page( $count, 10 );
        $show = $Page->show( );
        $list = $cloudgoods->where( $map )->order( array( "goods_id" => "desc" ) )->limit( $Page->firstRow.",".$Page->listRows )->select( );
        $user_ids = array( );
        foreach ($list as $k => $val ){
            $user_ids[$val['win_user_id']] = $val['win_user_id'];
            $sum = $cloudlogs->where( array("goods_id" => $val['goods_id'],"user_id" => $this->uid))->sum("num");
            $list[$k]['sum'] = $sum;
            if ( !empty( $val['win_user_id'] ) ){
                $sum2 = $cloudlogs->where( array("goods_id" => $val['goods_id'],"user_id" => $val['win_user_id']))->sum("num");
            }
            $list[$k]['sum2'] = $sum2;
        }
        $this->assign( "users", d( "Users" )->itemsByIds( $user_ids ) );
        $this->assign( "list", $list );
        $this->assign( "page", $show );
        $this->display( );
    }
	
	public function order( ){
        $obj = D("Cloudlogs");
        import("ORG.Util.Page");
        $map = array( "user_id" =>$this->uid);
        if (($bg_date = $this->_param( "bg_date", "htmlspecialchars" )) && ($end_date = $this->_param("end_date", "htmlspecialchars"))){
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array("ELT",$end_time),array("EGT",$bg_time));
            $this->assign("bg_date", $bg_date );
            $this->assign("end_date", $end_date );
        }
        else{
            if ($bg_date = $this->_param("bg_date", "htmlspecialchars")){
                $bg_time = strtotime($bg_date);
                $this->assign("bg_date", $bg_date);
                $map['create_time'] = array( "EGT",$bg_time);
            }
            if ($end_date = $this->_param( "end_date", "htmlspecialchars")){
                $end_time = strtotime($end_date);
                $this->assign("end_date", $end_date);
                $map['create_time'] = array("ELT",$end_time);
            }
        }
        if ($keyword = $this->_param( "keyword", "htmlspecialchars" ) ){
            $map['log_id'] = array("LIKE","%".$keyword."%");
            $this->assign("keyword", $keyword);
        }
        if (isset($_GET['st']) || isset($_POST['st'])){
            $st = (int)$this->_param("st");
            if ($st!= 999){
                $map['status'] = $st;
            }
            $this->assign("st", $st);
        }
        else{
            $this->assign("st", 999);
        }
        $count = $obj->where($map)->count();
        $Page = new Page( $count, 10 );
        $show = $Page->show();
        $list = $obj->where($map)->order(array("log_id" => "desc" ))->limit( $Page->firstRow.",".$Page->listRows )->select();
        $goods_ids = $user_ids = array( );
        foreach ($list as $k => $val ){
            $user_ids[$val['users_id']] = $val['users_id'];
			$goods_ids[$val['goods_id']] = $val['goods_id'];
        }
        $this->assign("users", D("Users")->itemsByIds($user_ids));
		$this->assign("cloudgoods", D("Cloudgoods")->itemsByIds($goods_ids));
        $this->assign("list", $list);
        $this->assign("page", $show);
        $this->display();
    }


    public function detail( $goods_id ){
        $p = (int)$this->_param( "p" );
        if (empty($p)){
            $p = 1;
        }
        $goods_id = (int)$goods_id;
        $obj = D("Cloudgoods");
        $cloudlogs = D( "Cloudlogs" );
        if ( !( $detail = $obj->find( $goods_id ) ) ){
            $this->error( "没有该商品" );
        }
        if ( $detail['closed'] != 0 || $detail['audit'] != 1 ){
            $this->error( "没有该商品" );
        }
        $list = $cloudlogs->where(array("goods_id" => $goods_id))->order(array( "log_id" => "asc" ))->select();
        $lists = $obj->get_datas( $list );
        $listss = array();
        foreach ( $lists as $k => $val ){
            if ( $val['user_id'] == $this->uid ){
                $listss[date( "Y-m-d H:i:s", $val['create_time'] ).".".$val['microtime']][] = $val;
            }
        }
        $this->assign( "list", $listss );
        if ( !empty( $detail['status'] ) || !empty( $detail['win_user_id'] ) ){
            $winner = D( "Users" )->find( $detail['win_user_id'] );
            $this->assign( "winner", $winner );
        }
        $this->assign( "total", $cloudlogs->where( array("goods_id" => $goods_id,"user_id" => $this->uid))->sum("num"));
        $this->assign( "m_list", $m_list );
        $this->assign( "detail", $detail );
        $this->assign( "p", $p );
        $this->display( );
    }

}
