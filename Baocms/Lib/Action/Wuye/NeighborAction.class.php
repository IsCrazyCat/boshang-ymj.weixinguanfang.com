<?php
class NeighborAction extends CommonAction
{
    public function index()
    {
        $this->assign('nextpage', LinkTo('neighbor/loadneighbor', array('t' => NOW_TIME, 'community_id' => $this->community_id, 'p' => '0000')));
        $this->display();
    }
    //贴吧邻居加载
    public function loadneighbor()
    {
        $Users = D('Communityusers');
        import('ORG.Util.Page');
        // 导入分页类 
        $map = array('community_id' => $this->community_id);
        $count = $Users->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 10);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }
        $list = $Users->where($map)->order($orderby)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        // 赋值数据集
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function delete($join_id = 0)
    {
        /*if ($detail['community_id'] != $this->community_id) {
              $this->error('请不要删除别人物业公司的业主！');
          }*/
        if (is_numeric($join_id) && ($join_id = (int) $join_id)) {
            $obj = D('Communityusers');
            if (!($detail = $obj->find($join_id))) {
                $this->error('该通知不存在');
            }
            if ($detail['community_id'] != $this->community_id) {
                $this->error('请不要删除他人好友');
            }
            $obj->where(array('join_id' => $join_id))->delete();
            $this->success('删除成功！', U('neighbor/index'));
        }
    }
}