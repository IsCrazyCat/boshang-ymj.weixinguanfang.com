<?php



class MylifeserviceAction extends CommonAction {

    public function index() {
        $Houseworksetting = D('Houseworksetting');//类目表
        $Housework = D('Housework');//报名表
        import('ORG.Util.Page'); // 导入分页类
        $map = array('user_id' => $this->uid);
        $count = $Housework->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Housework->where($map)->order(array('housework_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
		
        $houseworksetting_ids = array();
        foreach ($list as $k => $val) {
            $houseworksetting_ids[$val['id']] = $val['id'];
        }
		
        $this->assign('houseworksetting', $Houseworksetting->itemsByIds($houseworksetting_ids));
		
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板
    }

}
