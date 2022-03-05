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
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class UserLogin extends Middleware
{
    /**
     * 执行句柄
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @param mixed ...$guards
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     * @since 2020/8/31
     * @author 牧羊人
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $response = $next($request);

        $action = app('request')->route()->getAction();
        $controller = class_basename($action['controller']);
        list($controller, $action) = explode('@', $controller);
        $noLoginActs = ['LoginController'];
        $token = $request->headers->get('Authorization');
        if (strpos($token, 'Bearer ') !== false) {
            $token = str_replace("Bearer ", null, $token);
            // JWT解密token
            $jwt = new Jwt();
            $userId = $jwt->verifyToken($token);
        } else {
            $userId = 0;
        }
        if (!$userId && !in_array($controller, $noLoginActs)) {
            // 判断用户未登录就跳转至登录页面
            // 在这里可以定制你想要的返回格式, 亦或者是 JSON 编码格式
            return response()->json(message("请登录", false, null, 401));
        }
        //如果已登录则执行正常的请求
        return $response;
    }
}
