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
use Modules\Basics\Models\ConfigModel;

/**
 * 网站设置-服务类
 * @author 牧羊人
 * @since 2021/6/28
 * Class ConfigWebService
 * @package App\Services
 */
class ConfigWebService extends BaseService
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2021/6/28
     * ConfigWebService constructor.
     */
    public function __construct()
    {
        $this->model = new ConfigModel();
    }

    /**
     * 获取图片列表
     * @return array
     * @since 2021/6/28
     * @author 牧羊人
     */
    public function getList()
    {
        // 获取配置列表
        $configList = $this->model
            ->where("mark", "=", 1)
            ->orderBy("sort", "asc")
            ->get()
            ->toArray();
        $list = [];
        if ($configList) {
            $configDataModel = new ConfigDataModel();
            foreach ($configList as &$val) {
                $dataList = $configDataModel
                    ->where("config_id", "=", $val['id'])
                    ->where("mark", "=", 1)
                    ->orderBy("sort", "ASC")
                    ->get()
                    ->toArray();
                foreach ($dataList as &$v) {
                    if ($v['type'] == "array" || $v['type'] == "radio" || $v['type'] == "checkbox" || $v['type'] == "select") {
                        $data = preg_split('/[\r\n]+/s', $v['options']);
                        if ($data) {
                            $arr = [];
                            foreach ($data as $vt) {
                                $value = preg_split('/[:：]+/s', $vt);
                                $arr[$value[0]] = $value[1];
                            }
                            $v['param'] = $arr;
                        }
                        // 复选框
                        if ($v['type'] == "checkbox") {
                            $v['value'] = explode(",", $v['value']);
                        }
                    }
                    // 单图
                    if ($v['type'] == "image" && !empty($v['value'])) {
                        $v['value'] = get_image_url($v['value']);
                    }
                    // 多图
                    if ($v['type'] == "images") {
                        $urlList = explode(",", $v['value']);
                        $itemList = [];
                        foreach ($urlList as $vt) {
                            if (empty($vt)) {
                                continue;
                            }
                            $itemList[] = get_image_url($vt);
                        }
                        $v['value'] = $itemList;
                    }
                }
                $item = array();
                $item['config_id'] = $val['id'];
                $item['config_name'] = $val['name'];
                $item['item_list'] = $dataList;
                $list[] = $item;
            }
        }
        return message("操作成功", true, $list);
    }

    /**
     * 编辑表单
     * @return array
     * @since 2021/6/28
     * @author 牧羊人
     */
    public function edit()
    {
        // 参数
        $data = request()->all();
        if (!$data) {
            return message("参数不能为空", false);
        }
        foreach ($data as $key => &$val) {
            // 图片处理
            $preg = "/^http(s)?:\\/\\/.+/";
            if (is_string($val) && preg_match($preg, $val)) {
                if (strpos($val, "temp") !== false) {
                    $val = save_image($val, 'config');
                } else if (strpos($val, IMG_URL) !== false) {
                    $val = str_replace(IMG_URL, "", $val);
                }
            }
            if (is_array($val)) {
                $item = [];
                foreach ($val as $vt) {
                    $preg = "/^http(s)?:\\/\\/.+/";
                    if (preg_match($preg, $vt)) {
                        if (strpos($vt, "temp") !== false) {
                            $vt = save_image($vt, 'config');
                        } else {
                            $vt = str_replace(IMG_URL, "", $vt);
                        }
                        $item[] = $vt;
                    } else {
                        $item[] = $vt;
                    }
                }
                $val = !empty($item) ? implode(",", $item) : "";
            }
            $configDataModel = new ConfigDataModel();
            $result = $configDataModel->where("code", "=", $key)->first();
            $info = [];
            $info['id'] = $result['id'];
            $info['value'] = !empty($val) ? $val : "";
            $configDataModel->edit($info);
        }
        return message();
    }

}
