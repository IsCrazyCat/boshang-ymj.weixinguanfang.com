<?php
class Village_suggestionModel extends CommonModel
{
    protected $pk = 'id';
    protected $tableName = 'village_suggestion';
    protected $token = 'village_suggestion';
    protected $orderby = array('type' => 'desc', 'addtime' => 'desc');
}