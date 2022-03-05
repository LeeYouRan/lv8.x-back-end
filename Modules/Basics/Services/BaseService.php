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

/**
 * 服务基类
 * @author 牧羊人
 * @since 2020/11/10
 * Class BaseService
 * @package App\Services
 */
class BaseService
{
    // 模型
    protected $model;
    // 验证类
    protected $validate;

    /**
     * 获取数据列表
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function getList()
    {
        // 初始化变量
        $map = [];
        $sort = [['id', 'desc']];
        $is_sql = 0;

        // 获取参数
        $argList = func_get_args();
        if (!empty($argList)) {
            // 查询条件
            $map = (isset($argList[0]) && !empty($argList[0])) ? $argList[0] : [];
            // 排序
            $sort = (isset($argList[1]) && !empty($argList[1])) ? $argList[1] : [['id', 'desc']];
            // 是否打印SQL
            $is_sql = isset($argList[2]) ? isset($argList[2]) : 0;
        }

        // 打印SQL
        if ($is_sql) {
            $this->model->getLastSql(1);
        }

        // 常规查询条件
        $param = request()->input();
        if ($param) {
            // 筛选名称
            if (isset($param['name']) && $param['name']) {
                $map[] = ['name', 'like', "%{$param['name']}%"];
            }

            // 筛选标题
            if (isset($param['title']) && $param['title']) {
                $map[] = ['title', 'like', "%{$param['title']}%"];
            }

            // 筛选类型
            if (isset($param['type']) && $param['type']) {
                $map[] = ['type', '=', $param['type']];
            }

            // 筛选状态
            if (isset($param['status']) && $param['status']) {
                $map[] = ['status', '=', $param['status']];
            }

            // 手机号码
            if (isset($param['mobile']) && $param['mobile']) {
                $map[] = ['mobile', '=', $param['mobile']];
            }
        }

        // 设置查询条件
        if (is_array($map)) {
            $map[] = ['mark', '=', 1];
        } elseif ($map) {
            $map .= " AND mark=1 ";
        } else {
            $map .= " mark=1 ";
        }

        // 排序(支持多重排序)
        $query = $this->model->where($map)->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });
        // 分页条件
        $offset = (PAGE - 1) * PERPAGE;
        $result = $query->offset($offset)->limit(PERPAGE)->select('id')->get();
        $result = $result ? $result->toArray() : [];
        $list = [];
        if (is_array($result)) {
            foreach ($result as $val) {
                $info = $this->model->getInfo($val['id']);
                $list[] = $info;
            }
        }

        //获取数据总数
        $count = $this->model->where($map)->count();

        //返回结果
        $message = array(
            "msg" => '操作成功',
            "code" => 0,
            "data" => $list,
            "count" => $count,
        );
        return $message;
    }

    /**
     * 获取记录详情
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function info()
    {
        // 记录ID
        $id = request()->input("id", 0);
        $info = [];
        if ($id) {
            $info = $this->model->getInfo($id);
        }
        return message(MESSAGE_OK, true, $info);
    }

    /**
     * 添加或编辑记录
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function edit()
    {
        // 获取参数
        $argList = func_get_args();
        // 查询条件
        $data = isset($argList[0]) ? $argList[0] : [];
        // 是否打印SQL
        $is_sql = isset($argList[1]) ? $argList[1] : false;
        if (!$data) {
            $data = request()->all();
        }
        $error = '';
        $rowId = $this->model->edit($data, $error, $is_sql);
        if ($rowId) {
            return message();
        }
        return message($error, false);
    }

    /**
     * 删除记录
     * @return array
     * @since 2020/11/12
     * @author 牧羊人
     */
    public function delete()
    {
        // 参数
        $param = request()->all();
        // 记录ID
        $ids = getter($param, "id");
        if (empty($ids)) {
            return message("记录ID不能为空", false);
        }
        if (is_array($ids)) {
            // 批量删除
            $result = $this->model->deleteAll($ids);
            if (!$result) {
                return message("删除失败", false);
            }
            return message("删除成功");
        } else {
            // 单个删除
            $info = $this->model->getInfo($ids);
            if ($info) {
                $result = $this->model->drop($ids);
                if ($result !== false) {
                    return message();
                }
            }
            return message($this->model->getError(), false);
        }
    }

    /**
     * 设置记录状态
     * @return array
     * @since 2020/11/11
     * @author 牧羊人
     */
    public function status()
    {
        $data = request()->all();
        // 记录ID
        $id = getter($data, "id", 0);
        if (!$id) {
            return message('记录ID不能为空', false);
        }
        // 状态
        $status = getter($data, "status");
        if (!$status) {
            return message('记录状态不能为空', false);
        }
        $error = '';
        $item = [
            'id' => $id,
            'status' => $status
        ];
        $rowId = $this->model->edit($item, $error);
        if (!$rowId) {
            return message($error, false);
        }
        return message();
    }

}
