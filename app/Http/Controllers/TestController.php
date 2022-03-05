<?php
// +----------------------------------------------------------------------
// | RXThinkCMF_EVL8_PRO前后端分离旗舰版框架 [ RXThinkCMF ]
// +----------------------------------------------------------------------
// | 版权所有 2021 南京RXThinkCMF研发中心
// +----------------------------------------------------------------------
// | 官方网站: http://www.rxthink.cn
// +----------------------------------------------------------------------
// | 作者: 牧羊人 <rxthinkcmf@163.com>
// +----------------------------------------------------------------------
// | 免责声明:
// | 本软件框架禁止任何单位和个人用于任何违法、侵害他人合法利益等恶意的行为，禁止用于任何违
// | 反我国法律法规的一切平台研发，任何单位和个人使用本软件框架用于产品研发而产生的任何意外
// | 、疏忽、合约毁坏、诽谤、版权或知识产权侵犯及其造成的损失 (包括但不限于直接、间接、附带
// | 或衍生的损失等)，本团队不承担任何法律责任。本软件框架只能用于公司和个人内部的法律所允
// | 许的合法合规的软件产品研发，详细声明内容请阅读《框架免责声明》附件；
// +----------------------------------------------------------------------

namespace App\Http\Controllers;


use App\Models\Menu2Model;
use App\Models\MenuModel;

class TestController extends Backend
{

    public function index()
    {
        // 管理员(拥有全部权限)
        $menuModel = new MenuModel();
        $menuList = $menuModel->getChilds(0);
        if ($menuList) {
            foreach ($menuList as $val) {
                $data = [
                    'title' => trim($val['title']),
                    'icon' => trim($val['icon']),
                    'path' => trim($val['path']),
                    'component' => trim($val['component']),
                    'target' => trim($val['target']),
                    'pid' => 0,
                    'type' => intval($val['type']),
                    'permission' => trim($val['permission']),
                    'status' => 1,
                    'sort' => intval($val['sort']),
                    'create_user' => 1,
                    'create_time' => time(),
                    'update_user' => 1,
                    'update_time' => time(),
                ];
                $menuModel = new Menu2Model();
                $result = $menuModel->edit($data);
                if (!empty($val['children'])) {
                    foreach ($val['children'] as $vo) {
                        $data2 = [
                            'title' => trim($vo['title']),
                            'icon' => trim($vo['icon']),
                            'path' => trim($vo['path']),
                            'component' => trim($vo['component']),
                            'target' => trim($vo['target']),
                            'pid' => $result,
                            'type' => intval($vo['type']),
                            'permission' => trim($vo['permission']),
                            'status' => 1,
                            'sort' => intval($vo['sort']),
                            'create_user' => 1,
                            'create_time' => time(),
                            'update_user' => 1,
                            'update_time' => time(),
                        ];
                        $menuModel = new Menu2Model();
                        $result2 = $menuModel->edit($data2);
                        if (!empty($vo['children'])) {
                            foreach ($vo['children'] as $v) {
                                $data3 = [
                                    'title' => trim($v['title']),
                                    'icon' => trim($v['icon']),
                                    'path' => trim($v['path']),
                                    'component' => trim($v['component']),
                                    'target' => trim($v['target']),
                                    'pid' => $result2,
                                    'type' => intval($v['type']),
                                    'permission' => trim($v['permission']),
                                    'status' => 1,
                                    'sort' => intval($v['sort']),
                                    'create_user' => 1,
                                    'create_time' => time(),
                                    'update_user' => 1,
                                    'update_time' => time(),
                                ];
                                $menuModel = new Menu2Model();
                                $menuModel->edit($data3);
                            }
                        }
                    }
                }

            }
        }
        print_r($menuList);
        exit;
    }

}
