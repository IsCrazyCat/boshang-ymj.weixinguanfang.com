<?php
class XingeAction extends CommonAction
{
    //推送单发
    public function single()
    {
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('plat', 'title', 'contents'));
            $data['title'] = htmlspecialchars($data['title']);
            $data['contents'] = htmlspecialchars($data['contents']);
            $data['type'] = htmlspecialchars($data['plat']);
            if (!empty($data['url'])) {
                $data['url'] = htmlspecialchars($data['url']);
            }
            $result = D('Xinge')->single($data);
            if (true !== $result) {
                $this->baoError($result);
            }
            $this->baoSuccess('发送成功！', U('xinge/mass'));
        } else {
            $this->display();
        }
    }
    //推送群发
    public function mass()
    {
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('plat', 'title', 'contents', 'url'));
            $data['title'] = htmlspecialchars($data['title']);
            $data['contents'] = htmlspecialchars($data['contents']);
            if (!empty($data['url'])) {
                $data['url'] = htmlspecialchars($data['url']);
            }
            $data['url'] = htmlspecialchars($data['url']);
            $data['type'] = htmlspecialchars($data['plat']);
            $result = D('Xinge')->mass($data);
            if (true !== $result) {
                $this->baoError($result);
            }
            $this->baoSuccess('发送成功！', U('xinge/mass'));
        } else {
            $this->display();
        }
    }
}