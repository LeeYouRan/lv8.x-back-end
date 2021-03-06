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

use App\Models\NoticeModel;

/**
 * 通知公告-服务类
 * @author 牧羊人
 * @since 2020/11/11
 * Class NoticeService
 * @package App\Services
 */
class NoticeService extends BaseService
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/11
     * NoticeService constructor.
     */
    public function __construct()
    {
        $this->model = new NoticeModel();
    }

    /**
     * 添加或编辑
     * @return array
     * @since 2021/6/9
     * @author 牧羊人
     */
    public function edit()
    {
        // 请求参数
        $data = request()->all();
        //内容处理
        save_image_content($data['content'], $data['title'], "notice");
        return parent::edit($data); // TODO: Change the autogenerated stub
    }

    /**
     * 设置置顶
     * @return array
     * @since 2020/11/21
     * @author 牧羊人
     */
    public function setIsTop()
    {
        // 参数
        $data = request()->all();
        // 通知ID
        $id = getter($data, "id", 0);
        if (!$id) {
            return message('记录ID不能为空', false);
        }
        // 是否置顶
        $is_top = getter($data, "is_top", 0);
        if (!$is_top) {
            return message('设置置顶不能为空', false);
        }
        $error = '';
        $item = [
            'id' => $id,
            'is_top' => $is_top
        ];
        $rowId = $this->model->edit($item, $error);
        if (!$rowId) {
            return message($error, false);
        }
        return message();
    }

}
