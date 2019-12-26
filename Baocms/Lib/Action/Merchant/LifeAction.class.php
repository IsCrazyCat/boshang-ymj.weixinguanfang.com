<?php



class  LifeAction extends  CommonAction{
	
	 public function _initialize() {
        parent::_initialize();
		if ($this->_CONFIG['operation']['life'] == 0) {
				$this->error('此功能已关闭');die;
		}
    }
    
    public function index(){
        $Life = D('Life');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('user_id'=>  $this->uid); //分类信息是关联到UID 的 
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['qq|mobile|contact|title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Life->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Life->where($map)->order(array('last_time' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('cates', D('Lifecate')->fetchAll());
        $this->assign('channelmeans', D('Lifecate')->getChannelMeans());
        $this->display(); // 输出模板
    }
      public function urgent(){
        if(!$life_id = (int)  $this->_get('life_id')){
            $this->baoError('参数错误');
        }
        if(!$detail = D('Life')->find($life_id)){
            $this->baoError('参数错误');
        }
        if($detail['user_id'] != $this->uid){
             $this->baoError('参数错误');
        }
        
        $day = (int)$this->_get('day');
        $mday = 0;
        switch ($day){
            case 7:
               $mday = $day = 7;
                break;
            default:
                $day = 30;
                $mday = 27;
                break;
        }
        $money = $mday * $this->_CONFIG['shop']['life']['urgent']*100;
        if($this->member['money'] < $money){
            $this->baoErrorJump('余额不足',U('members/money/money'));
        }
        $urgent_date = date('Y-m-d',NOW_TIME + $day * 86400);
        if($detail['urgent_date'] > TODAY){
           $urgent_date = date('Y-m-d',strtotime($detail['urgent_date']) + $day*86400);
        }
         
        if(D('Users')->addMoney($this->uid, -$money,'加急信息'.$day.'天')){
            D('Life')->save(array('urgent_date'=>$urgent_date,'life_id'=>$life_id));
            $this->baoSuccess('您的信息已经加急！',U('life/index'));
        }
            
        $this->baoError('操作失败！');
    }
    
    public function top(){
        if(!$life_id = (int)  $this->_get('life_id')){
            $this->baoError('参数错误');
        }
        if(!$detail = D('Life')->find($life_id)){
            $this->baoError('参数错误');
        }
        if($detail['user_id'] != $this->uid){
             $this->baoError('参数错误');
        }
        
        $day = (int)$this->_get('day');
        $mday = 0;
        switch ($day){
            case 7:
               $mday = $day = 7;
                break;
            default:
                $day = 30;
                $mday = 27;
                break;
        }
        $money = $mday * $this->_CONFIG['shop']['life']['top']*100;
        if($this->member['money'] < $money){
            $this->baoErrorJump('余额不足',U('members/money/money'));
        }
        $top_date = date('Y-m-d',NOW_TIME + $day * 86400);
        if($detail['top_date'] > TODAY){
           $top_date = date('Y-m-d',strtotime($detail['top_date']) + $day*86400);
        }
         
        if(D('Users')->addMoney($this->uid, -$money,'置顶信息'.$day.'天')){
            D('Life')->save(array('top_date'=>$top_date,'life_id'=>$life_id));
            $this->baoSuccess('您的信息已经在同频道置顶了！',U('life/index'));
        }
            
        $this->baoError('操作失败！');
    }
    
    
    public function flush(){
        if(!$life_id = (int)  $this->_get('life_id')){
            $this->baoError('参数错误');
        }
        if(!$detail = D('Life')->find($life_id)){
            $this->baoError('参数错误');
        }
        if($detail['user_id'] != $this->uid){
             $this->baoError('参数错误');
        }
        if(NOW_TIME -$detail['last_time'] < 86400){
            $this->baoError('您已经刷新过了！');
        }
        if(NOW_TIME -$detail['last_time'] > (86400 * 30)){
            $this->baoError('该信息已经超过30天了，不能在进行免费刷新！');
        }
        $data = array(
            'life_id'   => $life_id,
            'last_time' => NOW_TIME
        );
        if($detail['top_date'] <TODAY){
           $data['top_date'] = TODAY;
        }
        if(D('Life')->save($data)){
            $this->baoSuccess('刷新成功!',U('life/index'));
        }
        $this->baoError('操作失败');        
    }
    
    
    
    
}