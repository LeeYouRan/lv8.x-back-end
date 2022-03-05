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

use Modules\Basics\Models\ConfigDataModel;

/**
 * 配置数据管理-服务类
 * @author 牧羊人
 * @since 2020/11/11
 * Class ConfigService
 * @package App\Services
 */
class ConfigDataService extends BaseService
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/11
     * ConfigService constructor.
     */
    public function __construct()
    {
        $this->model = new ConfigDataModel();
    }

    /**
     * 获取配置列表
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function getList()
    {
        $param = request()->all();
        // 查询条件
        $map = [];
        // 配置ID
        $configId = getter($param, "configId", 0);
        if ($configId) {
            $map[] = ['config_id', '=', $configId];
        }
        // 配置标题
        $title = getter($param, "title");
        if ($title) {
            $map[] = ['name', 'title', "%{$title}%"];
        }
        $list = $this->model->getList($map, [['sort', 'asc']]);
        return message("操作成功", true, $list);
    }

}
