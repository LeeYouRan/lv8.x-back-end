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

use App\Models\LinkModel;

/**
 * 友链管理-服务类
 * @author 牧羊人
 * @since 2020/11/11
 * Class LinkService
 * @package App\Services
 */
class LinkService extends BaseService
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/11
     * LinkService constructor.
     */
    public function __construct()
    {
        $this->model = new LinkModel();
    }

    /**
     * 获取友链列表
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function getList()
    {
        $param = request()->all();

        // 查询条件
        $map = [];

        // 友链平台
        $platform = getter($param, "platform");
        if ($platform) {
            $map[] = ["platform", '=', $platform];
        }
        return parent::getList($map); // TODO: Change the autogenerated stub
    }

    /**
     * 添加或编辑
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function edit()
    {
        $data = request()->all();

        // 图片处理
        $image = getter($data, "image");
        if (!empty($image)) {
            if (strpos($image, "temp") !== false) {
                $data['image'] = save_image($image, 'item');
            } else {
                $data['image'] = str_replace(IMG_URL, "", $image);
            }
        }
        return parent::edit($data); // TODO: Change the autogenerated stub
    }

}
