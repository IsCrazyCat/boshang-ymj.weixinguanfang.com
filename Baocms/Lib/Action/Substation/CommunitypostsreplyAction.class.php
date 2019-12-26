<?php

class CommunitypostsreplyAction extends CommonAction{
    private $create_fields = array('post_id','user_id','details');
    private $edit_fields = array('post_id','user_id','details');
    
     public  function index(){
       $Postreply = D('Communityreplys');
       import('ORG.Util.Page');// 导入分页类
		   
	    $map_community = array('city_id' => $this->city_id);
		$community_ids = D('Community')->where($map_community)->select();
		foreach ($community_ids as $val) {
			$community_idss[$val['community_id']] = $val['community_id'];//对比shop_id
		}
		//调用数据结束

        $map = array();
		$map['community_id']  = array('in',$community_idss);
		
		
       if ($post_id= (int) $this->_param('post_id')) {
            $map['post_id'] = $post_id;
            $this->assign('post_id', $post_id);
        }
        if ($user_id= (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $this->assign('user_id', $user_id);
        }
        if($audit = (int)$this->_param('audit')){
            $map['audit'] = ($audit === 1 ? 1:0);
            $this->assign('audit',$audit);
        }
       
       $count      = $Postreply->where($map)->count();// 查询满足要求的总记录数 
       $Page       = new Page($count,15);// 实例化分页类 传入总记录数和每页显示的记录数
       $show       = $Page->show();// 分页显示输出
	   
	   
       $list = $Postreply->where($map)->order(array('reply_id'=>'desc'))->limit($Page->firstRow.','.$Page->listRows)->select();
	   
	   
       $post_ids = $user_ids = array();
	   
       foreach($list  as  $k=>$val){
           $post_ids[$val['post_id']] = $val['post_id'];
           $user_ids[$val['user_id']] = $val['user_id'];
		   $community_ids[$val['community_id']] = $val['community_id'];//新增循环那个傻逼玩意
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $list[$k] = $val;
       }
	   
       if(!empty($post_ids)){
           $this->assign('posts',D('Communityposts')->itemsByIds($post_ids));
       }
       if(!empty($user_ids)){
           $this->assign('users',D('Users')->itemsByIds($user_ids));
       }
	   //新增循环那个傻逼玩意
	    if(!empty($community_ids)){
           $this->assign('community',D('Community')->itemsByIds($community_ids));
       }
	   
       
	   
	   
	   
       $this->assign('list',$list);// 赋值数据集
	   
	   
	 //  p($Postreply);//打印函数
	   
       $this->assign('page',$show);// 赋值分页输出
	   $this->assign('user',D('Users')->find($detail['user_id']));//查询那个傻逼
	  // $this->assign('community',D('Community')->find($detail['community_id']));//查询小区名字
       $this->display(); // 输出模板
    }






    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Communityreplys');
        if($obj->add($data)){
            $this->baoSuccess('添加成功',U('Communitypostsreply/index'));
        }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['post_id'] = (int)$data['post_id'];
        if(empty($data['post_id'])){
            $this->baoError('帖子ID不能为空');
        }        $data['user_id'] = (int)$data['user_id'];
        if(empty($data['user_id'])){
            $this->baoError('用户不能为空');
        }     
		
		   $data['details'] = SecurityEditorHtml($data['details']);
        if(empty($data['details'])){
            $this->baoError('回复内容不能为空2');
        }
		
		
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }
    public function edit($reply_id = 0){
        if($reply_id =(int) $reply_id){
            $obj = D('Communityreplys');
            if(!$detail = $obj->find($reply_id)){
                $this->baoError('请选择要编辑的回复帖子');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['reply_id'] = $reply_id;
                if(false!==$obj->save($data)){
                    $this->baoSuccess('操作成功',U('Communitypostsreply/index'));
                }
                $this->baoError('操作失败');
                
            }else{
                $this->assign('detail',$detail);      
                $this->assign('user',D('Users')->find($detail['user_id']));
                $this->display();
            }
        }else{
            $this->baoError('请选择要编辑的回复帖子');
        }
    }
     private function editCheck(){
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['post_id'] = (int)$data['post_id'];
        if(empty($data['post_id'])){
            $this->baoError('帖子ID不能为空');
        }        $data['user_id'] = (int)$data['user_id'];
        if(empty($data['user_id'])){
            $this->baoError('用户不能为空');
        }        $data['details'] = SecurityEditorHtml($data['details']);
        if(empty($data['details'])){
            $this->baoError('内容不能为空3');
        }
        return $data;  
    }

    public function delete($reply_id = 0){
         if(is_numeric($reply_id) && $reply_id = (int)$reply_id){
             $obj =D('Communityreplys');
             $obj->delete($reply_id);
             $this->baoSuccess('删除成功！',U('Communitypostsreply/index'));
         }else{
            $reply_id = $this->_post('reply_id',false);
            if(is_array($reply_id)){     
                $obj = D('Communityreplys');
                foreach($reply_id as $id){
                    $obj->delete($id);
                }                
                $this->baoSuccess('删除成功！', U('Communitypostsreply/index'));
            }
            $this->baoError('请选择要删除的回复帖子');
         }
         
    }


    public function audit($reply_id = 0){
       
         if(is_numeric($reply_id) && ($reply_id = (int)$reply_id)){
             $obj =D('Communityreplys');
              $detail = $obj->find($reply_id);
             $obj->save(array('reply_id'=>$reply_id,'audit'=>1));
              D('Users')->integral($detail['user_id'],'reply');
             $this->baoSuccess('审核成功！',U('Communitypostsreply/index'));
         }else{
            $reply_id = $this->_post('reply_id',false);
            if(is_array($reply_id)){     
                $obj = D('Communityreplys');
                foreach($reply_id as $id){
                   $detail = $obj->find($id);
                   $obj->save(array('reply_id'=>$id,'audit'=>1));
                   D('Users')->integral($detail['user_id'],'reply');
                }                
                $this->baoSuccess('审核成功！', U('Communitypostsreply/index'));
            }
            $this->baoError('请选择要审核的回复帖子');
         }
         
    }
    
   
}
