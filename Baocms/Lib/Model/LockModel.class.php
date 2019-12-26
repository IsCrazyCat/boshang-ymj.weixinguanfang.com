<?php
class LockModel extends CommonModel
{
    protected $pk = 'id';
    protected $tableName = 'lock';
    protected $id = 0;
    public function lock($uid)
    {
        $uid = (int) $uid;
        $t = date('mdHi', NOW_TIME);
        $this->id = $this->add(array('uid' => $uid, 't' => $t));
        return $this->id;
    }
    public function unlock()
    {
        return $this->delete($this->id);
    }
}