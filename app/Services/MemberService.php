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

namespace App\Services;

use App\Models\MemberModel;

/**
 * 会员管理-服务类
 * @author 牧羊人
 * @since 2020/11/11
 * Class MemberService
 * @package App\Services
 */
class MemberService extends BaseService
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/11
     * MemberService constructor.
     */
    public function __construct()
    {
        $this->model = new MemberModel();
    }

    /**
     * 获取数据列表
     * @return array
     * @since 2021/10/23
     * @author 牧羊人
     */
    public function getList()
    {
        // 参数
        $param = request()->all();

        // 查询条件
        $map = [];

        // 用户账号
        $username = getter($param, "username");
        if ($username) {
            $map[] = ["username", 'like', "%{$username}%"];
        }
        // 用户性别
        $gender = getter($param, "gender");
        if ($gender) {
            $map[] = ['gender', '=', $gender];
        }
        return parent::getList($map, [['id', 'desc']]); // TODO: Change the autogenerated stub
    }

    /**
     * 添加会编辑会员
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function edit()
    {
        // 请求参数
        $data = request()->all();
        // 用户名
        $username = getter($data, "username");
        // 密码
        $password = getter($data, "password");
        // 会员ID
        $memberId = getter($data, "id", 0);
        // 添加时设置密码
        if (empty($memberId)) {
            // 设置密码
            $data['password'] = get_password($password . $username);
            // 用户名重复性验证
            $count = $this->model
                ->where("username", '=', $username)
                ->where("mark", "=", 1)
                ->count();
            if ($count > 0) {
                return message("系统中已存在相同的会员名", false);
            }
        } else {
            // 用户名重复性验证
            $count = $this->model
                ->where("username", '=', $username)
                ->where("id", "<>", $memberId)
                ->where("mark", "=", 1)
                ->count();
            if ($count > 0) {
                return message("系统中已存在相同的用户名", false);
            }
            // 获取用户信息
            $info = $this->model->getInfo($memberId);
            if (!$info) {
                return message("用户信息不存在", false);
            }
            $data['password'] = $info['password'];
        }
        // 头像处理
        $avatar = getter($data, "avatar");
        if (!empty($avatar)) {
            if (strpos($avatar, "temp") !== false) {
                $data['avatar'] = save_image($avatar, 'member');
            } else {
                $data['avatar'] = str_replace(IMG_URL, "", $avatar);
            }
        }

        // 出生日期
        if (isset($data['birthday']) && $data['birthday']) {
            $data['birthday'] = strtotime($data['birthday']);
        }

        // 城市数据处理
        $city = isset($data['city']) ? $data['city'] : [];
        if (!empty($city)) {
            $data['province_code'] = $city[0];
            $data['city_code'] = $city[1];
            $data['district_code'] = $city[2];

            unset($data['city']);
        } else {
            $data['province_code'] = 0;
            $data['city_code'] = 0;
            $data['district_code'] = 0;
        }
        return parent::edit($data); // TODO: Change the autogenerated stub
    }

}
