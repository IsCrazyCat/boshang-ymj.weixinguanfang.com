<?php
class CleanAction extends CommonAction
{
    public function cache()
    {
        delFileByDir(APP_PATH . 'Runtime/');
        $time = NOW_TIME - 900;
        //15分钟的会删除
        M("session")->delete(array('where' => " session_expire < '{$time}' "));
        $this->success('更新缓存成功！', U('index/main'));
    }
}