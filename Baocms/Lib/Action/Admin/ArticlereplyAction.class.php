<?php
class ArticlereplyAction extends CommonAction
{
    private $create_fields = array('post_id', 'user_id', 'zan', 'content', 'audit');
    private $edit_fields = array('post_id', 'user_id', 'zan', 'content', 'audit');
    public function index()
    {
        $Articlecomment = D('Articlecomment');
        import('ORG.Util.Page');
        // 导入分页类
        $map = array();
        //搜索帖子ID
        //用户名搜索
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $this->assign('user_id', $user_id);
        }
        //审核状态
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = $audit === 1 ? 1 : 0;
            $this->assign('audit', $audit);
        }
        $count = $Articlecomment->where($map)->count();
        // 查询满足要求的总记录数
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = $Articlecomment->where($map)->order(array('comment_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $post_ids = $user_ids = array();
        foreach ($list as $k => $val) {
            $post_ids[$val['post_id']] = $val['post_id'];
            $user_ids[$val['user_id']] = $val['user_id'];
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
        }
        if (!empty($post_ids)) {
            $this->assign('posts', D('Article')->itemsByIds($post_ids));
        }
        if (!empty($user_ids)) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        }
        $this->assign('list', $list);
        // 赋值数据集
        //p($list);die;
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
        // 输出模板
    }
    public function create()
    {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Articlecomment');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('articlereply/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }
    private function createCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['post_id'] = (int) $data['post_id'];
        if (empty($data['post_id'])) {
            $this->baoError('帖子ID不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->baoError('内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($content)) {
            $this->baoError('内容含有敏感词：' . $words);
        }
        $data['zan'] = (int) $data['zan'];
        $data['audit'] = $this->_CONFIG['site']['article_reply_audit'];
        //回帖是否免审核。
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($comment_id = 0)
    {
        if ($comment_id = (int) $comment_id) {
            $obj = D('Articlecomment');
            if (!($detail = $obj->find($comment_id))) {
                $this->baoError('请选择要编辑的回复帖子');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['comment_id'] = $comment_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('articlereply/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的回复帖子');
        }
    }
    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['post_id'] = (int) $data['post_id'];
        if (empty($data['post_id'])) {
            $this->baoError('帖子ID不能为空');
        }
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('用户不能为空');
        }
        $data['content'] = SecurityEditorHtml($data['content']);
        if (empty($data['content'])) {
            $this->baoError('内容不能为空');
        }
        if ($words = D('Sensitive')->checkWords($content)) {
            $this->baoError('内容含有敏感词：' . $words);
        }
        $data['zan'] = (int) $data['zan'];
        $data['audit'] = $this->_CONFIG['site']['article_reply_audit'];
        //回帖是否免审核。
        return $data;
    }
    public function delete($comment_id = 0)
    {
        if (is_numeric($comment_id) && ($comment_id = (int) $comment_id)) {
            $obj = D('Articlecomment');
            $menu = $obj->fetchAll();
            foreach ($menu as $val) {
                if ($val['parent_id'] == $comment_id) {
                    $this->baoError('该回复下面还有其他回复');
                }
            }
            $obj->delete($comment_id);
            $this->baoSuccess('删除成功！', U('articlereply/index'));
        } else {
            $comment_id = $this->_post('comment_id', false);
            if (is_array($comment_id)) {
                $obj = D('Articlecomment');
                foreach ($comment_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('articlereply/index'));
            }
            $this->baoError('请选择要删除的回复帖子');
        }
    }
    public function audit($comment_id = 0)
    {
        if (is_numeric($comment_id) && ($comment_id = (int) $comment_id)) {
            $obj = D('Articlecomment');
            $detail = $obj->find($comment_id);
            $obj->save(array('comment_id' => $comment_id, 'audit' => 1));
            D('Users')->integral($detail['user_id'], 'reply');
            $this->baoSuccess('审核成功！', U('articlereply/index'));
        } else {
            $comment_id = $this->_post('comment_id', false);
            if (is_array($comment_id)) {
                $obj = D('Articlecomment');
                foreach ($comment_id as $id) {
                    $detail = $obj->find($id);
                    $obj->save(array('comment_id' => $id, 'audit' => 1));
                    D('Users')->integral($detail['user_id'], 'reply');
                }
                $this->baoSuccess('审核成功！', U('articlereply/index'));
            }
            $this->baoError('请选择要审核的回复帖子');
        }
    }
}