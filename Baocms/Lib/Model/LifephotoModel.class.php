<?php
class LifephotoModel extends CommonModel
{
    protected $pk = 'pic_id';
    protected $tableName = 'life_photos';
    public function upload($life_id, $photos)
    {
        $this->delete(array("where" => array('life_id' => $life_id)));
        foreach ($photos as $val) {
            $this->add(array('life_id' => $life_id, 'photo' => htmlspecialchars($val)));
        }
        return true;
    }
    public function getPics($life_id)
    {
        $life_id = (int) $life_id;
        return $this->where(array('life_id' => $life_id))->select();
    }
}