/**
 * Created by Administrator on 2016/3/10.
 */

window.switch = {

    getcart: function () {
        with (window) {
            if (!cookies.isset('ele')) {
                //购物车没商品
                return false;
            }
            var goods = cookies.get('ele');
            goods = cookies.parse(goods);
            return goods;
        }
    },
}



