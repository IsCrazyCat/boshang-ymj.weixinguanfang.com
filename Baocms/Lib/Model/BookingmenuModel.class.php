<?php
class BookingmenuModel extends CommonModel
{
    protected $pk = 'menu_id';
    protected $tableName = 'Booking_menu';
    public function get_menu($shop_id)
    {
        $cate = D('Bookingcate');
        $cate_list = $cate->where('shop_id = ' . $shop_id)->order(array('orderby' => 'asc'))->select();
        $menu = $this->where('closed=0 and shop_id=' . $shop_id)->select();
        $tem = array();
        foreach ($cate_list as $k => $v) {
            foreach ($menu as $kk => $vv) {
                if ($vv['cate_id'] == $v['cate_id']) {
                    $tem[$v['cate_id']][] = $vv;
                }
            }
        }
        return $tem;
    }
    public function get_cate($shop_id)
    {
        $menu = $this->get_menu($shop_id);
        $tem = array();
        foreach ($menu as $k => $v) {
            $tem[$k] = $k;
        }
        $cate = D('Bookingcate');
        $cate_list = $cate->order(array('orderby' => 'asc'))->itemsByIds($tem);
        return $cate_list;
    }
    public function get_count($shop_id)
    {
        $menu = $this->get_menu($shop_id);
        $cate = $this->get_cate($shop_id);
        $tem = array();
        foreach ($cate as $k => $v) {
            $tem[$k]['cate_name'] = $v['cate_name'];
            $tem[$k]['count'] = count($menu[$k]);
        }
        return $tem;
    }
    public function shop_menu($shop_id)
    {
        $tem = array();
        $menu = $this->where('shop_id = ' . $shop_id)->select();
        foreach ($menu as $k => $v) {
            $tem[$v['menu_id']] = $v;
        }
        return $tem;
    }
}