<?php
class AwardAction extends CommonAction {

    private function share($myshare_id, $share_id = 0) {
        $share = D('Awardshare')->find($share_id);
        if ($share['is_used'] && $myshare_id != $share_id) { //过滤本人
            $share['num'] = $share['num'] + 1;
            if ($share['num'] >= 5) {
                $share['num'] = 0;
                $share['is_used'] = 0;
            }

            D('Awardshare')->save($share);
        }
    }

    public function sorry($award_id) {
        $award_id = (int) $award_id;
        if (empty($award_id)) {
            $this->error('该活动不存在！');
        }
        $award = D('Award')->find($award_id);
        if (!$award || $award['expire_date'] < TODAY || $award['is_online'] != 1) {
            $this->error('该活动不存在或已经过期');
        }
        $share = D('Awardshare')->getdata($award_id);
        D('Awardshare')->save(array('id' => $share['id'], 'is_used' => 1));
        $award['code'] = baoQrCode('award_share' . $award_id . '_' . $share['id'], __HOST__ . U('award/index', array('award_id' => $award_id, 'share_id' => $share['id'])), 4);
        $this->assign('award', $award);
        $this->display();
    }

    public function index($award_id, $share_id = 0) {
        $award_id = (int) $award_id;
        if (empty($award_id)) {
            $this->error('该活动不存在！');
        }
        $award = D('Award')->find($award_id);
        if (!$award || $award['expire_date'] < TODAY || $award['is_online'] != 1) {
            $this->error('该活动不存在或已经过期');
        }
        if (!empty($award['shop_id'])) {
            $award['shop'] = D('Shop')->find($award['shop_id']);
        }
		$count = D('Awardshare')->get_count();
		$this->assign('count', $count);
        $award['share'] = D('Awardshare')->getdata($award_id); //判断一个人是否已经参加过活动
        if (!empty($share_id)) {
            $this->share($award['share']['id'], $share_id);
        }
        $award['code'] = baoQrCode('award_share' . $award_id . '_' . $award['share']['id'], __HOST__ . U('award/index', array('award_id' => $award_id, 'share_id' => $award['share']['id'])), 4);
        $this->assign('award', $award);
        $goods = D('Awardgoods')->where(array('award_id' => $award_id))->select();
        $this->assign('goods', $goods);

        switch ($award['type']) {
            case 'shark': //摇一摇
                if ($award['share']['is_used'] != 1) { //不等于一就继续
                    $this->getAward($goods);
                }
                $this->display('shark');
                break;
            case 'scratch': //刮刮卡
                $this->display('scratch');
                break;
            case 'lottery': //抽奖
                if ($award['share']['is_used'] != 1) { //不等于一就继续
                    $this->getAward($goods);
                }
                $this->display('lottery');
                break;
            default:
                $this->error('该活动不存在或已经过期');
                break;
        }
    }

    private function getAward($goods) {
        $num = 0;
        $jp = $goods2 = array();
        foreach ($goods as $val) {
            if (!empty($val['surplus'])) { //剔除没有库存的如果没有了
                $jp[$val['goods_id']]['start'] = $num;
                $goods2[$val['goods_id']] = $val;
                $num += $val['prob'];
                $jp[$val['goods_id']]['end'] = $num;
            }
        }
        if (!empty($jp) && !empty($num)) {
            $num2 = rand(1, $num);
            foreach ($jp as $key => $val) {
                if ($val['end'] > $num2) {
                    $this->assign('award_name', $goods2[$key]['award_name']);
                    session('award', $key); //将中间的ID存到SESSION
                    break;
                }
            }
        }
        return true;
    }

    public function winning() {
        $goodid = session('award');
        if (empty($goodid)) {
            $this->error('很抱歉没有您要领奖的奖品');
        }
        if (!$detail = D('Awardgoods')->find($goodid)) {
            $this->error('很抱歉没有您要领奖的奖品');
        }
        if (!$award = D('Award')->find($detail['award_id'])) {
            $this->error('很抱歉没有您要领奖的奖品');
        }

        if ($this->isPost()) {
            if (!$mobile = $this->_post('mobile')) {
                $this->error('请填写手机号码进行领奖');
            }
            if (!isMobile($mobile)) {
                $this->error('请填写正确的手机号码进行领奖');
            }

            if (D('Awardgoods')->updateCount($goodid, 'surplus', -1)) {
                session('award', null);
                $share = D('Awardshare')->getdata($detail['award_id']);
                D('Awardshare')->save(array('id' => $share['id'], 'is_used' => 1));
                D('Awardwinning')->add(array(
                    'award_id' => $award['award_id'],
                    'user_id' => $this->uid,
                    'name' => empty($this->member['nickname']) ? '游客' : $this->member['nickname'],
                    'goods_id' => $goodid,
                    'mobile' => $mobile,
                    'create_time' => NOW_TIME,
                    'create_ip' => $ip,
                ));
                $this->success('恭喜您领奖成功！请等待活动方联系！', U('award/index',array('award_id'=>$award['award_id'])));
            } else {
                $this->error('领取奖励失败');
            }
        } else {
            $url = U('Wap/award/index', array('award_id' => $award['award_id']));
            $url = __HOST__ . $url;
            $tooken = 'award_' . $award['award_id'];
            $award['photo'] = baoQrCode($tooken, $url);
            $this->assign('detail', $detail);
            $this->assign('award', $award);
            $this->display();
        }
    }

    public function iswinning() {
        $goodid = session('award');

        if ($goodid)
            die('1');
        else
            die('0');
    }

    //形成中奖图片的程序！刮刮卡
    public function scratch2($award_id) {

        $award_id = (int) $award_id;
        if (empty($award_id)) {
            $code = '未中奖';
        } else {
            $share = D('Awardshare')->getdata($award_id);
            $award = D('Award')->find($award_id);
            if (!$award || $share['is_used'] == 1 || $award['expire_date'] < TODAY || $award['is_online'] != 1) {
                $code = '未中奖';
            } else {
                $goods = D('Awardgoods')->where(array('award_id' => $award_id))->select();
                $num = 0;

                $jp = $goods2 = array();
                foreach ($goods as $val) {
                    if (!empty($val['surplus'])) { //剔除没有库存的如果没有了
                        $jp[$val['goods_id']]['start'] = $num;
                        $goods2[$val['goods_id']] = $val;
                        $num += $val['prob'];
                        $jp[$val['goods_id']]['end'] = $num;
                    }
                }
                if (empty($jp) || empty($num)) {
                    $code = '未中奖';
                } else {
                    $num2 = rand(1, $num);

                    foreach ($jp as $key => $val) {
                        if ($val['end'] > $num2) {
                            $code = $goods2[$key]['award_name'];
                            session('award', $key); //将中间的ID存到SESSION
                            break;
                        }
                    }
                }
            }
        }
        if (empty($code)) {
            $code = '未中奖';
        }
        import('ORG.Util.Image');
        Image::Baoaward($code);
    }
}