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

use Modules\Basics\Models\MemberLevelModel;

/**
 * 会员等级-服务类
 * @author 牧羊人
 * @since 2020/11/11
 * Class MemberLevelService
 * @package App\Services
 */
class MemberLevelService extends BaseService
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/11
     * MemberLevelService constructor.
     */
    public function __construct()
    {
        $this->model = new MemberLevelModel();
    }

    /**
     * 获取会员等级列表
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function getMemberLevelList()
    {
        $list = $this->model->where("mark", "=", 1)->get()->toArray();
        return message(MESSAGE_OK, true, $list);
    }

}
