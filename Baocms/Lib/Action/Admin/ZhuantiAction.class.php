<?php
class ZhuantiAction extends CommonAction
{
    //专题管理
    public function index()
    {
        $count = M('Zhuan')->count();
        // 查询满足要求的总记录数
        import('ORG.Util.Page');
        // 导入分页类
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $list = M('Zhuan')->order('sort asc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = implode(',', array_map(function ($item) {
            return $item['goods_id'];
        }, $list));
        $map = array('audit' => 1, 'closed' => 0);
        $goods = M('Tuan')->where($map)->select($ids);
        foreach ($list as $k => $lt) {
            foreach ($goods as $gd) {
                if ($gd['tuan_id'] == $lt['goods_id']) {
                    $data[$lt['floor_id']][] = array('map_id' => $lt['map_id'], 'zhuan_id' => $lt['zhuan_id'], 'floor_id' => $lt['floor_id'], 'tuan_id' => $gd['tuan_id'], 'title' => $gd['title'], 'photo' => $gd['photo'], 'deadline' => $lt['deadline'], 'sort' => $lt['sort']);
                    break;
                }
            }
        }
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('data', $data);
        $this->display();
    }
    //专题管理
    public function special()
    {
        $count = D('Zhuanmap')->count();
        // 查询满足要求的总记录数
        import('ORG.Util.Page');
        // 导入分页类
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $lists = D('Zhuanmap')->where(array('status' => 1))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('lists', $lists);
        $this->assign('page', $show);
        // 赋值分页输出
        $this->display();
    }
    public function addSpecial()
    {
        if ($data = $this->_post('data', false)) {
            $special = D('Zhuanmap');
            if (!$special->create($data)) {
                $this->baoError($special->getError());
            } else {
                if ($special->add()) {
                    $this->baoSuccess('专题创建成功！', U('zhuanti/special'));
                } else {
                    $this->baoError('专题创建失败！');
                }
            }
        } else {
            $this->display();
        }
    }
    public function editSpecial($map_id = null)
    {
        if (!$map_id) {
            $this->baoError('参数不正确！');
        } elseif ($data = $this->_post('data', false)) {
            $special = D('Zhuanmap');
            if (!$special->create($data)) {
                $this->baoError($special->getError());
            } else {
                if ($special->save()) {
                    $this->baoSuccess('专题编辑成功！', U('zhuanti/special'));
                } else {
                    $this->baoError('专题编辑失败！');
                }
            }
        } elseif (!($special = D('Zhuanmap')->find($map_id))) {
            $this->baoError('专题不存在或已删除!');
        } else {
            $this->assign('special', $special);
            $this->display();
        }
    }
    public function delSpecial($map_id = null)
    {
        if (!$map_id) {
            $this->baoError('参数不正确！');
        } elseif (!($special = D('Zhuanmap')->find($map_id))) {
            $this->baoError('专题不存在或已删除!');
        } elseif (D('Zhuan')->where("map_id={$map_id}")->find()) {
            $this->baoError('该专题下有商品,不能删除!');
        } elseif (D('Zhuanmap')->delete($map_id)) {
            $this->baoSuccess('删除成功！', U('zhuanti/special'));
        } else {
            $this->baoError('删除失败！');
        }
    }
    //添加商品到专题
    public function addGoods($goods_id = null)
    {
        if ($data = $this->_post('data', false)) {
            $floor = D('Zhuan');
            $data['deadline'] = strtotime($data['deadline']);
            $map = array('goods_id' => $data['goods_id'], 'floor_id' => $data['floor_id']);
            if ($floor->where($map)->find()) {
                $this->baoError('该楼层已存在该商品！');
            } elseif (!$floor->create($data)) {
                $this->baoError($floor->getError());
            } else {
                if ($floor->add()) {
                    $this->baoSuccess('添加商品到楼层成功！', U('zhuanti/index'));
                } else {
                    $this->baoError('添加商品到楼层失败!');
                }
            }
        } elseif (!$goods_id) {
            $this->baoError('参数不正确！');
        } else {
            $map = array('audit' => 1, 'closed' => 0);
            $goods = D('Tuan')->where($map)->find($goods_id);
            if (!$goods) {
                $this->baoError('商品不存在或未通过审核！');
            } else {
                $floors = D('Zhuanfloor')->order('sort asc')->select();
                $maps = D('Zhuanmap')->where('status=1')->select();
                $this->assign('floors', $floors);
                $this->assign('maps', $maps);
                $this->display();
            }
        }
    }
    //从专题移除商品
    public function delGoods($zhuan_id = null)
    {
        if (!$zhuan_id) {
            $this->baoError('参数不正确!');
        } else {
            if (!M('Zhuan')->find($zhuan_id)) {
                $this->baoError('该商品未添加在或已移除！');
            } else {
                if (M('Zhuan')->where("zhuan_id={$zhuan_id}")->delete()) {
                    $this->baoSuccess('从专题中移除商品成功！', U('zhuanti/index'));
                } else {
                    $this->baoError('从专题中移除商品失败！');
                }
            }
        }
    }
    //编辑专题的商品
    public function editGoods($zhuan_id = null)
    {
        if (!$zhuan_id) {
            $this->baoError('参数不正确!');
        } elseif ($data = $this->_post('data', false)) {
            $floor = D('Zhuan');
            $data['deadline'] = strtotime($data['deadline']);
            $map = array('goods_id' => $data['goods_id'], 'floor_id' => $data['floor_id']);
            if (!$floor->create($data)) {
                $this->baoError($floor->getError());
            } else {
                if ($floor->save()) {
                    $this->baoSuccess('编辑成功！', U('zhuanti/index'));
                } else {
                    $this->baoError('编辑失败!');
                }
            }
        } else {
            if (!($zhuan = M('Zhuan')->find($zhuan_id))) {
                $this->baoError('该商品未添加在或已移除！');
            } else {
                $floors = D('Zhuanfloor')->order('sort asc')->select();
                $this->assign('floors', $floors);
                $this->assign('zhuan', $zhuan);
                $maps = D('Zhuanmap')->where('status=1')->select();
                $this->assign('maps', $maps);
                $this->display();
            }
        }
    }
    //专题配置
    public function config()
    {
        $lists = D('Zhuanconfig')->where("status=1")->select();
        $this->assign('lists', $lists);
        $this->display();
    }
    public function addconfig()
    {
        if ($data = $this->_post('data', false)) {
            if (!empty($_FILES['photo']['name'][0]) || !empty($_FILES['photo']['name'][1])) {
                import('ORG.Net.UploadFile');
                $scope = date('Y') . '/' . date('m') . '/' . date('d') . '/';
                $upload = new UploadFile();
                // 实例化上传类
                $upload->uploadReplace = true;
                $upload->maxSize = 3145728;
                // 设置附件上传大小
                $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');
                // 设置附件上传类型
                $upload->savePath = './attachs/' . $scope;
                if (!$upload->upload()) {
                    $this->baoError($upload->getErrorMsg());
                } else {
                    $info = $upload->getUploadFileInfo();
                    $data['pc_banner'] = $scope . $info[0]['savename'];
                    $data['mobile_banner'] = $scope . $info[1]['savename'];
                }
            }
            $config = D('Zhuanconfig');
            if ($config->where("map_id={$data['map_id']}")->find()) {
                $this->baoError('你选的专题已配置过！');
            } elseif (!$config->create($data)) {
                $this->baoError($config->getError());
            } else {
                if ($config->add()) {
                    $this->baoSuccess('专题配置创建成功！', U('Zhuanti/config'));
                } else {
                    $this->baoError('专题配置创建失败！');
                }
            }
        } else {
            $maps = D('Zhuanmap')->where(array('status' => 1))->select();
            $this->assign('maps', $maps);
            $this->display();
        }
    }
    public function editconfig($config_id = null)
    {
        if (!$config_id) {
            $this->baoError('参数不正确！');
        } elseif (!($detail = D('Zhuanconfig')->find($config_id))) {
            $this->baoError('配置不存在或已被删除！');
        } elseif ($data = $this->_post('data', false)) {
            if (!empty($_FILES['photo']['name'][0]) || !empty($_FILES['photo']['name'][1])) {
                import('ORG.Net.UploadFile');
                $scope = date('Y') . '/' . date('m') . '/' . date('d') . '/';
                $upload = new UploadFile();
                // 实例化上传类
                $upload->uploadReplace = true;
                $upload->maxSize = 3145728;
                // 设置附件上传大小
                $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg');
                // 设置附件上传类型
                $upload->savePath = './attachs/' . $scope;
                if (!$upload->upload()) {
                    $this->baoError($upload->getErrorMsg());
                } else {
                    $info = $upload->getUploadFileInfo();
                    $data['pc_banner'] = $scope . $info[0]['savename'];
                    $data['mobile_banner'] = $scope . $info[1]['savename'];
                }
            }
            $config = D('Zhuanconfig');
            if ($detail['map_id'] != $data['map_id'] && $config->where("map_id={$data['map_id']}")->find()) {
                $this->baoError('该专题已经配置过了！');
            } elseif (!$config->create($data)) {
                $this->baoError($config->getError());
            } else {
                if ($config->save()) {
                    $this->baoSuccess('专题配置编辑成功！', U('Zhuanti/config'));
                } else {
                    $this->baoError('专题配置编辑失败！');
                }
            }
        } else {
            $maps = D('Zhuanmap')->where(array('status' => 1))->select();
            $this->assign('maps', $maps);
            $this->assign('detail', $detail);
            $this->display();
        }
    }
    public function delconfig($config_id = null)
    {
        if (!$config_id) {
            $this->baoError('参数不正确！');
        } elseif (!($detail = D('Zhuanconfig')->find($config_id))) {
            $this->baoError('配置不存在或已被删除！');
        } elseif (D('Zhuan')->where("map_id={$detail['map_id']}")->find()) {
            $this->baoError('删除失败,该配置被使用中！');
        } elseif (D('Zhuanconfig')->where("config_id={$config_id}")->setField('status', 0)) {
            $this->baoSuccess('配置删除成功！');
        } else {
            $this->baoError('配置删除失败！');
        }
    }
    public function floor()
    {
        $count = D('Zhuanfloor')->count();
        // 查询满足要求的总记录数
        import('ORG.Util.Page');
        // 导入分页类
        $Page = new Page($count, 15);
        // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();
        // 分页显示输出
        $floors = D('Zhuanfloor')->where("status=1")->limit($Page->firstRow . ',' . $Page->listRows)->order('sort asc')->select();
        $this->assign('page', $show);
        // 赋值分页输出
        $this->assign('floors', $floors);
        $this->display();
    }
    public function addfloor()
    {
        if ($data = I('post.data')) {
            $floor = D('Zhuanfloor');
            if (!$floor->create($data)) {
                $this->baoError($floor->getError());
            } else {
                if ($floor->add()) {
                    $this->baoSuccess('创建楼层成功！', U('zhuanti/floor'));
                } else {
                    $this->baoError('创建楼层失败!');
                }
            }
        } else {
            $this->display();
        }
    }
    public function editfloor($floor_id = null)
    {
        if ($data = I('post.data')) {
            $floor = D('Zhuanfloor');
            if (!$floor->create($data)) {
                $this->baoError($floor->getError());
            } else {
                if ($floor->save()) {
                    $this->baoSuccess('编辑楼层成功！', U('zhuanti/floor'));
                } else {
                    $this->baoError('编辑楼层失败');
                }
            }
        } else {
            if (!$floor_id) {
                $this->baoError('参数不正确');
            } else {
                if (!($floor = D('Zhuanfloor')->find($floor_id))) {
                    $this->baoError('楼层不存在或已删除！');
                } else {
                    $this->assign('floor', $floor);
                    $this->display();
                }
            }
        }
    }
    public function delFloor($floor_id = null)
    {
        if (!$floor_id) {
            $this->baoError('参数不正确！');
        } else {
            if (M('Zhuan')->where("floor_id={$floor_id}")->find()) {
                $this->baoError('楼层下有商品,请清空商品再删除楼层！');
            } else {
                if (D('Zhuanfloor')->where("floor_id={$floor_id}")->setField('status', 0)) {
                    $this->baoSuccess('操作成功', U('zhuanti/floor'));
                } else {
                    $this->baoError('删除失败！');
                }
            }
        }
    }
}