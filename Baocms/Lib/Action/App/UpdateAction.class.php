<?php
/*
create table app_version(`id` int(10) unsigned,ver int(12),downloadurl text, appstoreurl text,info text,size varchar(10),lastupdate int(10) unsigned);
*/
class UpdateAction extends CommonAction
{
    public function check()
    {
        $where = array('k' => 'updateapp');
        $list = D('setting')->where($where)->find();
        if (!$list) {
            die;
        }
        $list = unserialize($list['v']);
        $ver = addslashes($this->_get('ver'));
        $time = date('Y-m-d', $list['time']);
        if ($this->_get('platform') == '0') {
            $url = $list['appstoreurl'];
        } else {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/appupdate/app.apk';
        }
        if (UpdateAction::compareVersion($list['version'], $ver) == 1) {
            $data = array('status' => self::BAO_REQUEST_SUCCESS, 'version' => $list['version'], 'name' => $list['name'], 'url' => $url, 'time' => $time, 'info' => $list['info']);
            $this->stringify($data);
        }
        die;
    }
    private static function compareVersion($version1, $version2)
    {
        if ($version1 == $version2) {
            return 0;
        }
        $version1Array = explode(".", $version1);
        $version2Array = explode(".", $version2);
        $index = 0;
        $l = count($version1Array);
        $l2 = count($version2Array);
        $minLen = min($l, $l2);
        $diff = 0;
        while ($index < $minLen && ($diff = intval($version1Array[$index]) - intval($version2Array[$index])) == 0) {
            $index++;
        }
        if ($diff == 0) {
            for ($i = $index; $i < $l; $i++) {
                if (intval($version1Array[$i]) > 0) {
                    return 1;
                }
            }
            for ($i = $index; $i < $l2; $i++) {
                if (intval($version2Array[$i]) > 0) {
                    return -1;
                }
            }
            return 0;
        } else {
            return $diff > 0 ? 1 : -1;
        }
    }
}