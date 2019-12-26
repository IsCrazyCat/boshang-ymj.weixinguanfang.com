<?php
class TuanviewModel extends CommonModel
{
    protected $pk = 'view_id';
    protected $tableName = 'tuan_view';
    public function getViews($users_id, $tuan_id)
    {
        if (!empty($users_id)) {
            $result['user_id'] = $users_id;
            $result['tuan_id'] = $tuan_id;
            $result['create_time'] = NOW_TIME;
            $result['create_ip'] = get_client_ip();
            $res = $this->where(array('user_id' => $users_id, 'tuan_id' => $tuan_id))->find();
            if (!$res) {
                $this->add($result);
            } else {
                $result['view_id'] = $res['view_id'];
                $this->save($result);
            }
        }
    }
}