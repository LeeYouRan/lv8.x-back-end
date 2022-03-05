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

use Modules\Basics\Exports\Export;
use Modules\Basics\Imports\LevelImport;
use Modules\Basics\Models\LevelModel;
use Modules\Basics\Services\LevelService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * 职级管理-控制器
 * @author 牧羊人
 * @since 2020/11/11
 * Class LevelController
 * @package App\Http\Controllers
 */
class LevelController extends Backend
{

    /**
     * 构造函数
     * @author 牧羊人
     * @since 2020/11/11
     * LevelController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->model = new LevelModel();
        $this->service = new LevelService();
    }

    /**
     * 获取职级列表
     * @return mixed
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function getLevelList()
    {
        $result = $this->service->getLevelList();
        return $result;
    }

    /**
     * 导入Excel
     * @author 牧羊人
     * @since 2021/5/25
     */
    public function importExcel(Request $request)
    {
        $result = upload_file($request);
        if (!$result['success']) {
            return message($result['msg'], false);
        }
        // 文件路径
        $file_path = $result['data']['file_path'];
        if (!$file_path) {
            return message("文件上传失败", false);
        }
        // 文件绝对路径
        $file_path = ATTACHMENT_PATH . $file_path;
        // 导入Excel
        Excel::import(new LevelImport(), $file_path);
        return message("导入成功", true);
    }

    /**
     * 导出Excel
     * @author 牧羊人
     * @since 2021/4/10
     */
    public function exportExcel()
    {
        // 参数
        $param = request()->all();
        // 文件名称
        $fileName = date('YmdHis') . '.xlsx';
        // 表格头
        $header = ['职级ID', '职级名称', '职级状态', '排序'];
        // 获取数据源
        $result = $this->model->where("mark", "=", 1)->get()->toArray();
        $list = [];
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $data = [];
                $data['id'] = $val['id'];
                $data['name'] = $val['name'];
                $data['status'] = $val['status'] == 1 ? "在用" : "停用";
                $data['sort'] = $val['sort'];
                $list[] = $data;
            }
        }
        // 保存文件
        if (!Excel::store(new Export($list, $header, "职级列表"), "" . $fileName)) {
            return message(MESSAGE_FAILED, false);
        }
        // 移动文件
        copy(storage_path("app") . "/" . $fileName, UPLOAD_TEMP_PATH . "/" . $fileName);
        // 下载地址
        $fileUrl = get_image_url(str_replace(ATTACHMENT_PATH, "", UPLOAD_TEMP_PATH) . "/" . $fileName);
        return message(MESSAGE_OK, true, $fileUrl);
    }

}
