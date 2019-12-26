<?php



class PostAction extends CommonAction {

    public function bbs() {
        $Post = D('Post');
        import('ORG.Util.Page');
        $map = array('user_id' => $this->uid);
        $count = $Post->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();
        $list = $Post->where($map)->order('post_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $ids = array();
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
                $ids[$val['last_id']] = $val['last_id'];
            }
            $list[$k] = $val;
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    
    public function postedit($post_id = 0) {
        if ($post_id = (int) $post_id) {
            $obj = D('Post');
            if (!$detail = $obj->find($post_id)) {
                $this->baoError('请选择要编辑的消费分享');
            }
            if ($detail['user_id'] != $this->uid) {
                $this->error('请不要试图操作其他人的内容');
                die;
            }
            if ($this->isPost()) {
                $data = $this->postCheck();
                $data['post_id'] = $post_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('members/bbs'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('cates', D('Shopcate')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的消费分享');
        }
    }

    private function postCheck() {
        $data = $this->checkFields($this->_post('data', false), array('title', 'cate_id', 'details'));
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['user_id'] = (int) $this->uid;
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        }
        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('详细内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('详细内容含有敏感词：' . $words);
        }
        return $data;
    }

    public function post() {

        if ($this->isPost()) {
            $data = $this->postCheck();
            $obj = D('Post');
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();
            if ($obj->add($data)) {
                D('Users')->prestige($this->uid, 'share');
                $this->baoSuccess('添加成功', U('members/bbs'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Shopcate')->fetchAll());
            $this->display();
        }
    }

    public function postreply($post_id = 0) {
        if ($post_id = (int) $post_id) {
            $obj = D('Post');
            if (!$detail = $obj->find($post_id)) {
                $this->baoError('请选择要编辑的消费分享');
            }
            if ($this->isPost()) {
                $data = $this->checkFields($this->_post('data', false), array('contents'));
                $data['post_id'] = $post_id;
                $data['user_id'] = $this->uid;
                $data['contents'] = SecurityEditorHtml($data['contents']);
                if (empty($data['contents'])) {
                    $this->baoError('内容不能为空');
                }
                if ($words = D('Sensitive')->checkWords($data['contents'])) {
                    $this->baoError('详细内容含有敏感词：' . $words);
                }
                $data['create_time'] = NOW_TIME;
                $data['create_ip'] = get_client_ip();
                $obj = D('Postreply');
                if ($obj->add($data)) {
                    D('Post')->save(array('post_id' => $post_id, 'last_id' => $this->uid, 'last_time' => NOW_TIME));
                    D('Users')->prestige($this->uid, 'reply');
                    $this->baoSuccess('回复成功', U('share/detail', array('post_id' => $post_id)));
                }
                $this->baoError('操作失败！');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的消费分享');
        }
    }

    
}
