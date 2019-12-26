<?php if (!defined('THINK_PATH')) exit(); if(is_array($list)): foreach($list as $key=>$order): ?><li class="line ">
        <dt>
            <a class="x3">订单ID：<?php echo ($order["order_id"]); ?></a>
            <a class="x9 text-right">下单时间：<?php echo (date('Y-m-d H:i:s',$order["create_time"])); ?></span> </a>
        </dt>

        <?php if(is_array($goods)): foreach($goods as $key=>$good): if($good['order_id'] == $order['order_id']): ?><dd class="zhong">
                    <div class="x4">
                        <img src="<?php echo config_img($products[$good['goods_id']]['photo']);?>" width="70" height="70">
                    </div>
                    <div class="x8">
                        <p><a href="<?php echo U('goods/detail',array('order_id'=>$order['order_id']));?>"><?php echo ($products[$good['goods_id']]['title']); ?> </a>
                        </p>

                        <p class="text-small">
                            <span class="text-dot1 margin-right">小计：<span class="text-dot">&yen;<?php echo round($good['price']/100,2);?> x <?php echo ($good["num"]); ?> = &yen;<?php echo round($good['total_price']/100,2);?> 元</span></span>
                        </p>
                        <?php if(!empty($good[key_name])): ?><p class="text-small">
                                <span class="text-dot1 margin-right">规格：<span class="text-dot"><?php echo ($good["key_name"]); ?></span></span>
                            </p><?php endif; ?>

                    </div>
                </dd><?php endif; endforeach; endif; ?>

        <dt>
            <div class="x12">
             <span class="margin-right">

             实际支付：
             <?php if(($order["status"]) == "0"): ?>未支付
             <?php else: ?>
             <a class="text-dot"> &yen;<?php echo round($order['need_pay']/100,2);?></a> 元<?php endif; ?>
                 </eq></span>
            </div>
        </dt>
        <dl>
            <p class="text-right padding-top x12">
                <?php switch($order["status"]): case "0": ?><a class="button button-small bg-dot"
                           href="<?php echo u('wap/mall/pay',array('type'=>goods,'order_id'=>$order['order_id'],'address_id'=>$order['address_id']));?>"
                           target="_blank">预约中</a>
                        <a target="x-frame" class="button button-small bg-gray"
                           href="<?php echo U('goods/orderdel',array('order_id'=>$order['order_id']));?>">取消订单</a><?php break;?>

                    <?php case "1": ?><span class="button button-small bg-dot">预约成功</span>
<!--                        <a target="x-frame" class="button button-small bg-gray"-->
<!--                           href="<?php echo U('goods/refund',array('order_id'=>$order['order_id']));?>">申请退款</a>--><?php break;?>
<!--                    <?php case "4": ?>-->
<!--                        <a target="x-frame" class="button button-small bg-dot"-->
<!--                           href="<?php echo U('goods/cancel_refund',array('order_id'=>$order['order_id']));?>">取消退款</a>-->
<!--<?php break;?>-->

                    <?php case "2": ?><a target="x-frame" class="button button-small bg-blue"
                                       href="<?php echo U('goods/queren',array('order_id'=>$order['order_id']));?>">已完成</a><?php break;?>
                    <?php case "8": if(($order["is_dianping"]) == "0"): ?><a class="button button-small bg-blue"
                               href="<?php echo U('goods/dianping',array('order_id'=>$order['order_id']));?>">我要评价</a><?php endif; ?>
                        <?php if(($order["is_dianping"]) == "1"): ?><a class="button button-small bg-gray">已评价</a><?php endif; break; endswitch;?>

                <a class="button button-small bg-blue"
                   href="<?php echo U('goods/detail',array('order_id'=>$order['order_id']));?>">详情</a>
            </p>
        </dl>
    </li>
    <div class="blank-10 bg"></div><?php endforeach; endif; ?>