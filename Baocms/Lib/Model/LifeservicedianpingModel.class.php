<?php
class LifeservicedianpingModel extends CommonModel
{
    protected $pk = 'activity_id';
    protected $tableName = 'lifeservice_dianping';
    public function check($id, $user_id)
    {
        $data = $this->find(array('where' => array('id' => (int) $id, 'user_id' => (int) $user_id)));
        return $this->_format($data);
    }
    public function CallDataForMat($items)
    {
        //专门针对CALLDATA 标签处理的
        if (empty($items)) {
            return array();
        }
        $obj = D('Users');
        $user_ids = array();
        foreach ($items as $k => $val) {
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $users = $obj->itemsByIds($user_ids);
        foreach ($items as $k => $val) {
            $val['user'] = $users[$val['user_id']];
            $items[$k] = $val;
        }
        return $items;
    }
}