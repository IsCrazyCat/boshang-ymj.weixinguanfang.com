<?php
class RunningAction extends CommonAction{
	
    public function index(){
        $running = D('Running');
        import('ORG.Util.Page');
        $map = array( 'closed' => 0);  
		$map = array( );
        if ( $keyword = $this->_param( "keyword", "htmlspecialchars" ) ){
            $map['title'] = array("LIKE","%".$keyword."%");
            $this->assign( "keyword", $keyword );
        }
		if (isset($_GET['st']) || isset($_POST['st'])) {
            $st = (int) $this->_param('st');
            if ($st != 999) {
                $map['status'] = $st;
            }
            $this->assign('st', $st);
        } else {
            $this->assign('st', 999);
        }
        $count = $running->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
		
        $list = $running->where($map)->order('running_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
		$this->assign('types', D('Running')->getType());
        $this->display();
       
    }

    public function detail( $running_id ){
        $running_id = ( integer )$running_id;
        if ( empty( $running_id ) || !( $detail = D( "Running" )->find( $running_id ) ) ){
            $this->error( "该跑腿不存在" );
        }
        $this->assign( "detail", $detail );
        $this->display( );
    }

   
     public function delete($running_id = 0) {
		$running_id = (int) $running_id;
		$obj = D('Running');
		$detail = $obj->find($running_id);
        if($detail['status'] !=0 || $detail['status'] !=8){
            $this->baoError('状态错误');
        } else {
            $obj->save(array('running_id' => $running_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('running/index'));
        }
    }
	
    
}


