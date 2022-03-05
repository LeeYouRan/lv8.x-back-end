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


use App\Models\Example2Model;

/**
 * 演示案例二管理-服务类
 * @author 牧羊人
 * @since: 2021/10/23
 * Class Example2Service
* @package App\Services
 */
class Example2Service extends BaseService
{
    /**
     * 构造函数
     * @author 牧羊人
     * @since 2021/10/23
     * LevelService constructor.
     */
    public function __construct()
    {
        $this->model = new Example2Model();
    }

	/**
     * 获取数据列表
     * @return array
     * @since 2021/10/23
     * @author 牧羊人
     */
    public function getList()
    {
        $param = request()->all();

        // 查询条件
        $map = [];
	
	    // 测试名称
        $name = isset($param['name']) ? trim($param['name']) : '';
        if ($name) {
            $map[] = ['name', 'like', "%{$name}%"];
        }
		
	    // 性别
        $gender = isset($param['gender']) ? (int)$param['gender'] : 0;
        if ($gender) {
            $map[] = ['gender', '=', $gender];
        }
		
	    // 状态
        $status = isset($param['status']) ? (int)$param['status'] : 0;
        if ($status) {
            $map[] = ['status', '=', $status];
        }
	
        return parent::getList($map); // TODO: Change the autogenerated stub
    }

	/**
     * 添加或编辑
     * @return array
     * @since 2021/10/23
     * @author 牧羊人
     */
    public function edit()
    {
        // 参数
        $data = request()->all();
	                                            
		// 头像处理
        $avatar = trim($data['avatar']);
        if (strpos($avatar, "temp")) {
            $data['avatar'] = save_image($avatar, 'example2');
        } else {
            $data['avatar'] = str_replace(IMG_URL, "", $data['avatar']);
        }
                                                                                                            
        return parent::edit($data); // TODO: Change the autogenerated stub
    }

                        	                            
}
