<?php
class VoteoptionModel extends CommonModel {

    protected $pk = 'option_id';
    protected $tableName = 'vote_option';

    public function upload($vote_id, $photos) {
        $vote_id = (int) $vote_id;
        $option_id = (int) $option_id;
        $this->delete(array("where" => array('option_id' => $option_id)));
        foreach ($photos as $val) {
            $this->add(array('option' => $val, 'vote_id' => $vote_id));
        }
        return true;
    }
    
    

    public function getPics($option_id) {
        $option_id = (int) $option_id;
        return $this->where(array('option_id' => $option_id))->select();
    }

}
