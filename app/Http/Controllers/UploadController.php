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

use Illuminate\Http\Request;

/**
 * 上传文件-控制器
 * @author 牧羊人
 * @since 2020/11/11
 * Class UploadController
 * @package App\Http\Controllers
 */
class UploadController extends Backend
{

    /**
     * 上传图片
     * @param Request $request 网络请求
     * @return array
     * @author 牧羊人
     * @since 2020/11/11
     */
    public function uploadImage(Request $request)
    {
        // 上传单图统一调取方法
        $result = upload_image($request, 'file');
        if (!$result['success']) {
            return message($result['msg'], false);
        }

        // 文件路径
        $file_path = $result['data']['img_path'];
        if (!$file_path) {
            return message("文件上传失败", false);
        }

        // 网络域名拼接
        if (strpos($file_path, IMG_URL) === false) {
            $file_path = IMG_URL . $file_path;
        }

        // 返回结果
        return message(MESSAGE_OK, true, $file_path);
    }

    /**
     * 上传文件
     * @param Request $request
     * @return array
     * @author 牧羊人
     * @since 2021/7/12
     */
    public function uploadFile(Request $request)
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
        // 网络域名拼接
        if (strpos($file_path, IMG_URL) === false) {
            $file_path = IMG_URL . $file_path;
        }
        // 返回结果
        $this->jsonReturn(MESSAGE_OK, true, $file_path);
    }

}
