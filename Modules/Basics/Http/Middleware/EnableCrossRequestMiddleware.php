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

namespace Modules\Basics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * 跨域解决方案
 * @author 牧羊人
 * @since 2021/1/10
 * Class EnableCrossRequestMiddleware
 * @package App\Http\Middleware
 */
class EnableCrossRequestMiddleware
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
        $response = $next($request);
        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '';
        $allow_origin = [
            'http://localhost:8080',
            'http://localhost:8081',
            'http://manage.evl.pro.rxthink.cn',
            'http://lv8.x-back-end:7888',
        ];
        if (in_array($origin, $allow_origin)) {
            //允许所有资源跨域
            $response->header('Access-Control-Allow-Origin', $origin);
            // 允许通过的响应报头
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
            // 允许axios获取响应头中的Authorization
            $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
            // 允许的请求方法
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
            //允许的请求方法
            $response->header('Allow', 'GET, POST, PATCH, PUT, OPTIONS, delete');
            // 运行客户端携带证书式访问
            $response->header('Access-Control-Allow-Credentials', 'true');
        }
        return $response;
    }
}
