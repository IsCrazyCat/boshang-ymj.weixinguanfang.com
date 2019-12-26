<?php



class DingcateAction extends CommonAction {

    private $create_fields = array('cate_name', 'orderby');
    private $edit_fields = array('cate_name', 'orderby');

    public function _initialize() {
        parent::_initialize();
        if (empty($this->shop['is_ding'])) {
            $this->error('订座功能要和网站洽谈，由网站开通！');
        }
    }

    public function index() {
		$Shopdingcate = D('Shopdingcate');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>'0');
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['cate_name'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($shop_id = $this->shop_id) {
            $map['shop_id'] = $shop_id;
            $shop = D('Shop')->find($shop_id);
            $this->assign('shop_name', $shop['shop_name']);
            $this->assign('shop_id', $shop_id);
        }

        $count = $Shopdingcate->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Shopdingcate->where($map)->order(array('cate_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $shop_ids = array();
        foreach ($list as $k => $val) {
            if ($val['shop_id']) {
                $shop_ids[$val['shop_id']] = $val['shop_id'];
            }
        }
        if ($shop_ids) {
            $this->assign('shops', D('Shop')->itemsByIds($shop_ids));
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板
		
		
    }
	
	
	public function create() {
        if (IS_AJAX) {
            $shop_id = $this->shop_id;
			$cate_name = I('cate_name','','trim,htmlspecialchars');
			if(empty($cate_name)){
				$this->ajaxReturn(array('status'=>'error','message'=>'分类名称不能为空！'));
			}
            $obj = D('Shopdingcate');
			$data = array(
				'shop_id'=>$shop_id,
				'cate_name'=>$cate_name,
				'num'=>0,
				'closed'=>0
			);
            if ($obj->add($data)) {
				$this->ajaxReturn(array('status'=>'success','message'=>'添加成功！'));
            }
            $this->ajaxReturn(array('status'=>'error','message'=>'添加失败！'));
        }
    }



	public function edit(){

	    if(IS_AJAX){
			
			$cate_id = I('v','','intval,trim');
			
			if ($cate_id) {
				
				$obj = D('Shopdingcate');
				
				if (!$detail = $obj->find($cate_id)) {
					$this->ajaxReturn(array('status'=>'error','message'=>'请选择要编辑的菜单分类！'));
				}
				if ($detail['shop_id'] != $this->shop_id) {
					$this->ajaxReturn(array('status'=>'error','message'=>'请不要操作其他商家的菜单分类！'));
				}
				$cate_name = I('cate_name','','trim,htmlspecialchars');
				if (empty($cate_name)) {
					$this->ajaxReturn(array('status'=>'error','message'=>'分类名称不能为空！'));
				}
				
				$data = array(
					'cate_name'=>$cate_name,
				);
				if (false !== $obj->where('cate_id ='.$cate_id)->setField($data)) {
					$this->ajaxReturn(array('status'=>'success','message'=>'操作成功！'));
				}
				$this->ajaxReturn(array('status'=>'error','message'=>'操作失败！'));
			} else {
				$this->ajaxReturn(array('status'=>'error','message'=>'请选择要编辑的菜单分类！'));
			}
		
		}
	
    }
	

	
}
