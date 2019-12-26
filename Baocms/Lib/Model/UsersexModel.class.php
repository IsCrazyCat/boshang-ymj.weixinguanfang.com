<?php
class UsersexModel extends CommonModel
{
    protected $pk = 'user_id';
    protected $tableName = 'users_ex';
    public function getUserex($user_id)
    {
        $user_id = (int) $user_id;
        $data = $this->find($user_id);
        if (empty($data)) {
            $data = array('user_id' => $user_id, 'last_uid' => 0, 'views' => 0);
            $this->add($data);
        }
        return $data;
    }
    public function getStar()
    {
        return array('1' => '白羊座', '2' => '金牛座', '3' => '双子座', '4' => '巨蟹座', '5' => '狮子座', '6' => '处女座', '7' => '天秤座', '8' => '天蝎座', '9' => '射手座', '10' => '魔蝎座', '11' => '水瓶座', '12' => '双鱼座');
    }
}