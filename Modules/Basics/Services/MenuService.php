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

namespace Modules\Basics\Services;


use Modules\Basics\Models\MenuModel;

/**
 * 菜单管理-服务类
 * @author 牧羊人
 * @since 2020/11/10
 * Class MenuService
 * @package App\Services
 */
class MenuService extends BaseService
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/11
     * MenuService constructor.
     */
    public function __construct()
    {
        $this->model = new MenuModel();
    }

    /**
     * 获取数据列表
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function getList()
    {
        $param = request()->all();

        // 查询条件
        $map = [];
        // 菜单标题
        $title = getter($param, "title");
        if ($title) {
            $map[] = ['title', 'like', "%{$title}%"];
        }
        $list = $this->model->getList($map, [['sort', 'asc']]);
        return message("操作成功", true, $list);
    }

    /**
     * 获取菜单详情
     * @return array
     * @since 2021/4/10
     * @author 牧羊人
     */
    public function info()
    {
        // 记录ID
        $id = request()->input("id", 0);
        $info = [];
        if ($id) {
            $info = $this->model->getInfo($id);
        }
        // 获取权限节点
        $checkedList = array();
        if ($info['type'] == 0) {
            $permissionList = $this->model
                ->select("sort")
                ->where("pid", "=", $info['id'])
                ->where("type", "=", 1)
                ->where("mark", "=", 1)
                ->get()
                ->toArray();
            if (is_array($permissionList)) {
                $checkedList = array_key_value($permissionList, "sort");
            }
            $info['checkedList'] = $checkedList;
        }
        return message(MESSAGE_OK, true, $info);
    }

    /**
     * 添加或编辑
     * @return array
     * @since 2021/4/10
     * @author 牧羊人
     */
    public function edit()
    {
        // 参数
        $param = request()->all();
        // 权限节点
        $checkedList = isset($param['checkedList']) ? $param['checkedList'] : array();
        unset($param['checkedList']);
        // 保存数据
        $result = $this->model->edit($param);
        if (!$result) {
            return message("操作失败", false);
        }

        // 设置权限节点
        $isFunc = false;
        if ($param['type'] == 0 && !empty($param['path']) && !empty($checkedList)) {
            $item = explode("/", $param['path']);
            // 模块名称
            $moduleName = $item[count($item) - 1];
            // 模块标题
            $moduleTitle = str_replace("管理", "", $param['title']);
            // 删除以存在的节点
            $funcIds = $this->model
                ->where("pid", $result)
                ->where("type", "=", 1)
                ->select('id')
                ->get();
            $funcIds = $funcIds ? $funcIds->toArray() : [];
            $this->model->deleteAll($funcIds, true);
            $isFunc = true;
            // 遍历权限节点
            foreach ($checkedList as $val) {
                $data = [];
                $data['pid'] = $result;
                $data['type'] = 1;
                $data['status'] = 1;
                $data['sort'] = intval($val);
                $data['target'] = $param['target'];

                // 判断当前节点是否已存在
                $permissionInfo = $this->model
                    ->where("pid", "=", $result)
                    ->where("type", "=", 1)
                    ->where("sort", "=", $val)
                    ->where("mark", "=", 1)
                    ->first();
                if ($permissionInfo) {
                    $data['id'] = $permissionInfo['id'];
                }
                if ($val == 1) {
                    // 查询
                    $data['title'] = "查询" . $moduleTitle;
                    $data['permission'] = "sys:{$moduleName}:index";
                } else if ($val == 5) {
                    // 添加
                    $data['title'] = "添加" . $moduleTitle;
                    $data['permission'] = "sys:{$moduleName}:add";
                } else if ($val == 10) {
                    // 修改
                    $data['title'] = "修改" . $moduleTitle;
                    $data['permission'] = "sys:{$moduleName}:edit";
                } else if ($val == 15) {
                    // 删除
                    $data['title'] = "删除" . $moduleTitle;
                    $data['permission'] = "sys:{$moduleName}:delete";
                } else if ($val == 20) {
                    // 状态
                    $data['title'] = "设置状态";
                    $data['permission'] = "sys:{$moduleName}:status";
                } else if ($val == 25) {
                    // 批量删除
                    $data['title'] = "批量删除";
                    $data['permission'] = "sys:{$moduleName}:dall";
                } else if ($val == 30) {
                    // 全部展开
                    $data['title'] = "全部展开";
                    $data['permission'] = "sys:{$moduleName}:expand";
                } else if ($val == 35) {
                    // 全部折叠
                    $data['title'] = "全部折叠";
                    $data['permission'] = "sys:{$moduleName}:collapse";
                } else if ($val == 40) {
                    // 添加子级
                    $data['title'] = "添加子级";
                    $data['permission'] = "sys:{$moduleName}:addz";
                } else if ($val == 45) {
                    // 导出数据
                    $data['title'] = "导出数据";
                    $data['permission'] = "sys:{$moduleName}:export";
                } else if ($val == 50) {
                    // 导入数据
                    $data['title'] = "导入数据";
                    $data['permission'] = "sys:{$moduleName}:import";
                } else if ($val == 55) {
                    // 分配权限
                    $data['title'] = "分配权限";
                    $data['permission'] = "sys:{$moduleName}:permission";
                }
                if (empty($data['title'])) {
                    continue;
                }
                $menuModel = new MenuModel();
                $menuModel->edit($data);
            }
        }
        return message("操作成功", true, $isFunc);
    }

    /**
     * 获取权限菜单列表
     * @param $userId
     * @return array
     * @author 牧羊人
     * @since 2020/11/11
     */
    public function getPermissionList($userId)
    {
        $list = [];
        if ($userId == 1) {
            // 管理员拥有全部权限
            $list = $this->model->getChilds(0);
        } else {
            // 其他角色
            $list = $this->getPermissionMenu($userId, 0);
        }
        return message("操作成功", true, $list);
    }

    /**
     * 获取菜单权限列表
     * @param $userId 用户ID
     * @param $pid 上级ID
     * @return mixed
     * @since 2020/11/14
     * @author 牧羊人
     */
    public function getPermissionMenu($userId, $pid = 0)
    {
        $menuModel = new MenuModel();
        $menuList = $menuModel::from("menu as m")
            ->select('m.*')
            ->join('role_menu as rm', 'rm.menu_id', '=', 'm.id')
            ->join('user_role as ur', 'ur.role_id', '=', 'rm.role_id')
            ->distinct(true)
            ->where('ur.user_id', '=', $userId)
            ->where('m.type', '=', 0)
            ->where('m.pid', '=', $pid)
            ->where('m.status', '=', 1)
            ->where('m.mark', '=', 1)
            ->orderBy('m.pid')
            ->orderBy('m.sort')
            ->get()->toArray();
        if (!empty($menuList)) {
            foreach ($menuList as &$val) {
                $childList = $this->getPermissionMenu($userId, $val['id']);
                if (is_array($childList) && !empty($childList)) {
                    $val['children'] = $childList;
                }
            }
        }
        return $menuList;
    }

    /**
     * 获取权限节点
     * @param $userId 用户ID
     * @return array
     * @author 牧羊人
     * @since 2021/4/9
     */
    public function getPermissionsList($userId)
    {
        $list = [];
        if ($userId == 1) {
            // 管理员拥有全部权限
            $permissionList = $this->model
                ->distinct(true)
                ->select("permission")
                ->where("type", "=", 1)
                ->where("mark", "=", 1)
                ->get()
                ->toArray();
            $list = empty($permissionList) ? array() : array_key_value($permissionList, 'permission');
        } else {
            // 其他角色
            $menuModel = new MenuModel();
            $permissionList = $menuModel::from("menu as m")
                ->select('m.permission')
                ->join('role_menu as rm', 'rm.menu_id', '=', 'm.id')
                ->join('user_role as ur', 'ur.role_id', '=', 'rm.role_id')
                ->distinct(true)
                ->where('ur.user_id', '=', $userId)
                ->where('m.type', '=', 1)
//                ->where('m.status', '=', 1)
                ->where('m.mark', '=', 1)
//                ->orderBy('m.pid')
//                ->orderBy('m.sort')
                ->get()
                ->toArray();
            $list = empty($permissionList) ? array() : array_key_value($permissionList, 'permission');
        }
        return $list;
    }

}
