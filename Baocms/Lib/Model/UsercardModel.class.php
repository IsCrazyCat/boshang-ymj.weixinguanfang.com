<?php
class UsercardModel extends CommonModel {

    protected $pk = 'card_id';
    protected $tableName = 'user_card';

    
    public function checkCard($code) {
        $code = (int) $code;
        return $this->find(array('where' => array('card_num' => $code)));
    }

}
