
<?php
//傻逼破解的

class ExpressAction extends CommonAction{
	
	 private $create_fields = array('user_id','city_id','area_id', 'business_id','title','from_name','from_addr','from_mobile','to_name','to_addr','to_mobile','lat','lng');
    private $edit_fields = array('user_id','city_id','area_id', 'business_id','title','from_name','from_addr','from_mobile','to_name','to_addr','to_mobile','lat','lng');

    public function index(){
        $express = D('Express');
        import('ORG.Util.Page');
        $map = array( 'closed' => 0,'city_id'=>$this->city_id);  
		$map = array( );
        if ( $keyword = $this->_param( "keyword", "htmlspecialchars" ) ){
            $map['title'] = array("LIKE","%".$keyword."%");
            $this->assign( "keyword", $keyword );
        }
		
        $count = $express->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
		
        $list = $express->where($map)->order('express_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
       
    }

    public function detail( $express_id ){
        $express_id = ( integer )$express_id;
        if ( empty( $express_id ) || !( $detail = d( "Express" )->find( $express_id ) ) ){
            $this->error( "该快递不存在" );
        }
        if ( $detail['user_id'] != $this->uid ){
            $this->error( "请不要操作他人的快递" );
        }
        $this->assign( "detail", $detail );
        $this->display( );
    }

    public function create( ){
        if ( $this->isPost( ) ){
            $data = $this->createCheck( );
            if ( $express_id = D( "Express" )->add( $data ) ){
                $this->baoSuccess( "发布成功", u( "express/index" ) );
            }
            $this->baoError( "发布失败" );
        }
        else{
            $this->display( );
        }
    }

    public function createCheck( ){
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
		
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
		
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('发布人会员不能为空');
        }
        $data['title'] = htmlspecialchars( $data['title'] );
        if ( empty( $data['title'] ) ){
            $this->baoError( "标题不能为空" );
        }
        $data['from_name'] = htmlspecialchars( $data['from_name'] );
        if ( empty( $data['from_name'] ) ){
            $this->baoError( "寄件人姓名不能为空" );
        }
        $data['from_addr'] = htmlspecialchars( $data['from_addr'] );
        if ( empty( $data['from_addr'] ) ){
            $this->baoError( "寄件人地址不能为空" );
        }
        $data['from_mobile'] = htmlspecialchars( $data['from_mobile'] );
        if ( empty( $data['from_mobile'] ) ){
            $this->baoError( "寄件人手机不能为空" );
        }
        if ( !ismobile( $data['from_mobile'] ) ){
            $this->baoError( "寄件人手机格式不正确" );
        }
        $data['to_name'] = htmlspecialchars( $data['to_name'] );
        if ( empty( $data['to_name'] ) ){
            $this->baoError( "收件人姓名不能为空" );
        }
        $data['to_addr'] = htmlspecialchars( $data['to_addr'] );
        if ( empty( $data['to_addr'] ) ){
            $this->baoError( "收件人地址不能为空" );
        }
        $data['to_mobile'] = htmlspecialchars( $data['to_mobile'] );
        if ( empty( $data['to_mobile'] ) ){
            $this->baoError( "收件人手机不能为空" );
        }
        if ( !ismobile( $data['to_mobile'] ) ){
            $this->baoError( "收件人手机格式不正确" );
        }
        
		$data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip( );
        return $data;
    }

    public function edit($express_id = 0 ){
		 if ($express_id = (int) $express_id) {
			 
			 
			//查询上级ID编辑处代码开始
			$city_ids = D('Express')->find($express_id);
			$citys = $city_ids['city_id'];
			if ($citys != $this->city_id) {
			   $this->error('非法操作', U('express/index'));
			}
			//查询上级ID编辑处代结束
		
		
            $obj = D('Express');
            if (!$detail = $obj->find($express_id)) {
                $this->baoError('请选择要编辑的快递1');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['express_id'] = $express_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('express/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user',D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的快递2');
        }
		
		
    }

    public function editCheck( ){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
		$data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('城市不能为空');
        }
		
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('地区不能为空');
        }
		
		$data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('发布人会员不能为空');
        }
        $data['title'] = htmlspecialchars( $data['title'] );
        if ( empty( $data['title'] ) ){
            $this->baoError( "标题不能为空" );
        }
        $data['from_name'] = htmlspecialchars( $data['from_name'] );
        if ( empty( $data['from_name'] ) ){
            $this->baoError( "寄件人姓名不能为空" );
        }
        $data['from_addr'] = htmlspecialchars( $data['from_addr'] );
        if ( empty( $data['from_addr'] ) ){
            $this->baoError( "寄件人地址不能为空" );
        }
        $data['from_mobile'] = htmlspecialchars( $data['from_mobile'] );
        if ( empty( $data['from_mobile'] ) ){
            $this->baoError( "寄件人手机不能为空" );
        }
        if ( !ismobile( $data['from_mobile'] ) ){
            $this->baoError( "寄件人手机格式不正确" );
        }
        $data['to_name'] = htmlspecialchars( $data['to_name'] );
        if ( empty( $data['to_name'] ) ){
            $this->baoError( "收件人姓名不能为空" );
        }
        $data['to_addr'] = htmlspecialchars( $data['to_addr'] );
        if ( empty( $data['to_addr'] ) ){
            $this->baoError( "收件人地址不能为空" );
        }
        $data['to_mobile'] = htmlspecialchars( $data['to_mobile'] );
        if ( empty( $data['to_mobile'] ) ){
            $this->baoError( "收件人手机不能为空" );
        }
        if ( !ismobile( $data['to_mobile'] ) ){
            $this->baoError( "收件人手机格式不正确" );
        }
        
		$data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip( );
        return $data;
    }


     public function delete($express_id = 0) {
        if (is_numeric($express_id) && ($express_id = (int) $express_id)) {
			
			//查询上级ID编辑处代码开始
			$city_ids = D('Express')->find($express_id);
			$citys = $city_ids['city_id'];
			if ($citys != $this->city_id) {
			   $this->error('非法操作', U('express/index'));
			}
			//查询上级ID编辑处代结束
			
			
            $obj = D('Express');
            $obj->save(array('express_id' => $express_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('express/index'));
        } else {
            $express_id = $this->_post('express_id', false);
            if (is_array($express_id)) {
                $obj = D('Express');
                foreach ($express_id as $id) {
                     $obj->save(array('express_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('express/index'));
            }
            $this->baoError('请选择要删除的快递');
        }
    }
	
    
}


