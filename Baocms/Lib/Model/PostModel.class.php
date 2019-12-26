<?php
class PostModel extends CommonModel
{
    protected $pk = 'post_id';
    protected $tableName = 'post';
    protected $token = 'post_count';
    public function countbycate()
    {
        //
        $cache = cache(array('type' => 'File', 'expire' => 60));
        if (!($data = $cache->get($this->token))) {
            $datas = $this->query("select  count(1) as num,cate_id from " . $this->getTableName() . " group  by  cate_id");
            $data2 = $this->query("select  max(last_time) as last_time,cate_id from " . $this->getTableName() . " group  by  cate_id");
            $last_t = array();
            foreach ($data2 as $val) {
                $last_t[$val['cate_id']] = $val['last_time'];
            }
            $counts = array();
            foreach ($datas as $val) {
                $counts[$val['cate_id']] = $val['num'];
            }
            $data = array();
            $cates = D('Shopcate')->fetchAll();
            foreach ($cates as $k => $val) {
                if ($val['parent_id'] == 0) {
                    $max = array();
                    foreach ($cates as $val2) {
                        if ($val2['parent_id'] == $val['cate_id']) {
                            $data[$val['cate_id']]['count'] += $counts[$val2['cate_id']];
                            $max[] = $last_t[$val2['cate_id']];
                        }
                    }
                    $data[$val['cate_id']]['last_time'] = max($max);
                    $data[$val['cate_id']]['last_time'] = formatt($data[$val['cate_id']]['last_time']);
                }
            }
            $cache->set($this->token, $data);
        }
        return $data;
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