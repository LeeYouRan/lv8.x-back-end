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

namespace App\Http\Middleware;

use App\Helpers\Jwt;
use App\Helpers\JwtUtils;
use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 验证访问权限
        $action = app('request')->route()->getAction();
        $controller = class_basename($action['controller']);
        list($controller, $action) = explode('@', $controller);

        // 排除无需验证的控制器
        $noLoginActs = ['LoginController', 'IndexController'];
        if (!in_array($controller, $noLoginActs)) {
            // 控制器名
            $controller = strtolower(str_replace("Controller", null, $controller));
            $permission = "sys:{$controller}:{$action}";

            // 获取用户ID
            $userId = JwtUtils::getUserId();
            if ($userId != 1) {
                // 权限节点列表
                $menuService = new MenuService();
                $permissionList = $menuService->getPermissionsList($userId);
                if (!in_array($permission, $permissionList)) {
                    return response()->json(message("暂无权限", false, null, 403));
                }
            }
        }
        return $next($request);
    }
}
