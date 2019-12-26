<?php
class LifeModel extends CommonModel
{
    protected $pk = 'life_id';
    protected $tableName = 'life';
    protected $_validate = array(array(), array(), array());
    public function randTop()
    {
        $lifes = $this->where(array('audit' => 1, 'top_date' => array('EGT', TODAY)))->order(array('last_time' => 'desc'))->limit(0, 45)->select();
        //print_r($this->getLastSql());
        shuffle($lifes);
        if (empty($lifes)) {
            return array();
        }
        $num = count($lifes) > 9 ? 9 : count($lifes);
        $keys = array_rand($lifes, $num);
        $return = array();
        foreach ($lifes as $k => $val) {
            if (in_array($k, $keys)) {
                $return[] = $val;
            }
        }
        return $return;
    }
}