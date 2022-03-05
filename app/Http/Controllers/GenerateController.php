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


use App\Services\GenerateService;

/**
 * 代码生成器-控制器
 * @author 牧羊人
 * @since 2020/11/12
 * Class GenerateController
 * @package App\Http\Middleware
 */
class GenerateController extends Backend
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/12
     * GenerateController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new GenerateService();
    }

    /**
     * 一键生成模块
     * @return mixed
     * @since 2020/11/12
     * @author 牧羊人
     */
    public function generate()
    {
        // 参数
        $param = request()->all();
        $result = $this->service->generate($param);
        return $result;
    }

    /**
     * 批量生成模块
     * @return array
     * @since 2021/10/23
     * @author 牧羊人
     */
    public function batchGenerate()
    {
        // 参数
        $param = request()->all();
        // 表信息
        $tables = isset($param['tables']) ? $param['tables'] : [];
        if (empty($tables)) {
            return message("请选择数据表", false);
        }
        foreach ($tables as $val) {
            $item = explode(",", $val);
            $data = [
                'name' => $item[0],
                'comment' => $item[1],
            ];
            $this->service->generate($data);
        }
        return message(sprintf("本次共生成【%d】个模块", count($tables)));

    }

}
