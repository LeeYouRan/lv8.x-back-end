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

use App\Models\ActionLogModel;
use App\Services\LoginService;

/**
 * 登录控制器
 * @author 牧羊人
 * @since 2020/11/10
 * Class LoginController
 * @package App\Http\Controllers
 */
class LoginController extends Backend
{

    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/10
     * LoginController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new LoginService();
    }

    /**
     * 获取验证码
     * @return mixed
     * @since 2020/11/10
     * @author 牧羊人
     */
    public function captcha()
    {
        $result = $this->service->captcha();
        return $result;
    }

    /**
     * 系统登录
     * @author 牧羊人
     * @since 2020/11/10
     */

    /**
     * @OA\Post(path="/login",
     *   tags={"用户登录"},
     *   summary="用户登录",
     *   @OA\Parameter(name="username", in="query", description="用户名", @OA\Schema(type="string")),
     *   @OA\Parameter(name="password", in="query", description="密码", @OA\Schema(type="string")),
     *   @OA\Parameter(name="captcha", in="query", description="验证码", @OA\Schema(type="string")),
     *   @OA\Parameter(name="key", in="query", description="验证key", @OA\Schema(type="string")),
     *   @OA\Response(response="200", description="successful operation")
     * )
     */
    public function login()
    {
        $result = $this->service->login();
        return $result;
    }

    /**
     * 退出系统
     * @author 牧羊人
     * @since 2020/11/10
     */
    public function logout()
    {
        $result = $this->service->logout();
        return $result;
    }

}
