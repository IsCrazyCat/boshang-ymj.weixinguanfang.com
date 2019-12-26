<?php
class TuisongduanxinAction extends CommonAction {
	public function _initialize() {
        parent::_initialize();
        $this->assign('ranks',D('Userrank')->fetchAll());
		
    }
    private $create_fields = array('tuisong_id',  'title', 'create_time','rank_id','user_id');
    private $edit_fields = array('tuisong_id',  'title', 'create_time','rank_id','user_id');

    public function index() {
        $Tuisongduanxin = D('Tuisongduanxin');
        import('ORG.Util.Page'); 
        $map = array();
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Tuisongduanxin->where($map)->count(); 
        $Page = new Page($count, 25); 
        $show = $Page->show(); 
        $list = $Tuisongduanxin->where($map)->order(array('tuisong_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show); 
        $this->display(); 
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Tuisongduanxin');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('Tuisongduanxin/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
		$data['user_id'] = (int) $data['user_id'];
		$data['rank_id'] = (int) $data['rank_id'];
        $data['create_time'] = NOW_TIME;
        return $data;
    }

    public function delete($tuisong_id = 0) {
        if (is_numeric($tuisong_id) && ($tuisong_id = (int) $tuisong_id)) {
            $obj = D('Tuisongduanxin');
            $obj->delete($tuisong_id);
            $this->baoSuccess('删除成功！', U('Tuisongduanxin/index'));
        } else {
            $tuisong_id = $this->_post('tuisong_id', false);
            if (is_array($tuisong_id)) {
                $obj = D('Tuisongduanxin');
                foreach ($tuisong_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('Tuisongduanxin/index'));
            }
            $this->baoError('请选择要删除的手机消息');
        }
    }
	
	 public function tuisong($tuisong_id){
		$tuisong_id= (int) $this->_param('tuisong_id');
        $Tuisongduanxin = D('Tuisongduanxin');
        if (!$detail = $Tuisongduanxin->find($tuisong_id)) {
            $this->error('请选择要推送的消息');
        }
		$db  = D("Users"); 
		$map['mobile'] = array('neq',''); //邮箱不能为空
		if(!empty($detail['rank_id'])){
			$map['rank_id'] = array('eq',$detail['rank_id']);//用户等级发送
		}
		if(!empty($detail['user_id'])){
			$map['user_id'] = array('eq',$detail['user_id']);//后台选择单条推送就显示执行
		}
		$users = $db ->where($map)->field('mobile')-> select(); //取得所有用户的邮箱 Email 字段对应你的 数据库邮箱的那个字段
		foreach($users as $value){
			if($this->_CONFIG['sms']['dxapi'] == 'dy'){
				D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_push_mass_sms', $value['mobile'], array(
					'sitename'=>$this->_CONFIG['site']['sitename'], 
					'title'=>$detail['title']//短信内容
				));
			}else{
				$this->baoError('您没开启大鱼短信，发送失败');
			}
		}
		$Tuisongduanxin->where("$tuisong_id=%d",$detail['tuisong_id'])->save(array('is_tuisong'=>1,'create_time'=>NOW_TIME,));//更新数据库
		$this->baoSuccess('短信发送成功',U('tuisongduanxin/index'));
    }

}
