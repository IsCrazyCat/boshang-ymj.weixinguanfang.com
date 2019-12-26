<?php
class LifedetailsModel extends CommonModel
{
    protected $pk = 'life_id';
    protected $tableName = 'life_details';
    public function updateDetails($life_id, $details)
    {
        $data = $this->find($life_id);
        if ($data) {
            $this->save(array('life_id' => $life_id, 'details' => $details));
        } else {
            $this->add(array('life_id' => $life_id, 'details' => $details));
        }
        return true;
    }
}