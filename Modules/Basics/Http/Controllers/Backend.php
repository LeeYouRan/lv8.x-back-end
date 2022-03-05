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

namespace Modules\Basics\Http\Controllers;

use Modules\Basics\Helpers\JwtUtils;
use Modules\Basics\Models\UserModel;
use Illuminate\Support\Facades\Session;

/**
 * 后台控制器基类
 * @author 牧羊人
 * @since 2020/11/10
 * Class Backend
 * @package App\Http\Controllers
 */
class Backend extends BaseController
{
    // 模型
    protected $model;
    // 服务
    protected $service;
    // 校验
    protected $validate;
    // 登录ID
    protected $userId;
    // 登录信息
    protected $userInfo;

    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/10
     * Backend constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // 初始化配置
        $this->initConfig();

        // 登录检测中间件
        $this->middleware('user.login');

        // 权限检测中间件
        $this->middleware('check.auth');

        // 初始化登录信息
        $this->middleware(function ($request, $next) {
            $userId = JwtUtils::getUserId();

            // 登录验证
            $this->initLogin($userId);

            return $next($request);
        });
    }

    /**
     * 初始化配置
     * @author 牧羊人
     * @since 2020/11/10
     */
    public function initConfig()
    {
        // 请求参数
        $this->param = \request()->input();

        // 分页基础默认值
        defined('PERPAGE') or define('PERPAGE', isset($this->param['limit']) ? $this->param['limit'] : 20);
        defined('PAGE') or define('PAGE', isset($this->param['page']) ? $this->param['page'] : 1);
    }

    /**
     * 登录验证
     * @param $userId 用户ID
     * @return
     * @author 牧羊人
     * @since 2020/8/31
     */
    public function initLogin($userId)
    {
        // 登录用户ID
        $this->userId = $userId;

        // 登录用户信息
        if ($userId) {
            $adminModel = new UserModel();
            $userInfo = $adminModel->getInfo($this->userId);
            $this->userInfo = $userInfo;
        }

    }

    /**
     * 获取数据列表
     * @return mixed
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function index()
    {
        $result = $this->service->getList();
        return $result;
    }

    /**
     * 获取数据详情
     * @return mixed
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function info()
    {
        $result = $this->service->info();
        return $result;
    }

    /**
     * 添加或编辑
     * @return mixed
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function edit()
    {
        $result = $this->service->edit();
        return $result;
    }

    /**
     * 删除数据
     * @return mixed
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function delete()
    {
        $result = $this->service->delete();
        return $result;
    }

    /**
     * 设置状态
     * @return mixed
     * @since 2020/11/21
     * @author 牧羊人
     */
    public function status()
    {
        $result = $this->service->status();
        return $result;
    }

    /**
     * 批量删除
     * @return array
     * @since 2021/10/23
     * @author 牧羊人
     */
    public function batchDelete()
    {
        if (IS_POST) {
            $ids = explode(',', $_POST['id']);
            //批量删除
            $num = 0;
            foreach ($ids as $key => $val) {
                $res = $this->model->drop($val);
                if ($res !== false) {
                    $num++;
                }
            }
            return message('本次共选择' . count($ids) . "个条数据,删除" . $num . "个");
        }
    }

}
