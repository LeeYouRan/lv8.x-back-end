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

namespace Modules\Basics\Models;

use Modules\Basics\Helpers\Jwt;
use Modules\Basics\Helpers\JwtUtils;
use Illuminate\Support\Facades\Request;

/**
 * 缓存基类
 * @author zongjl
 * @date 2019/5/23
 * Class BaseModel
 * @package App\Models
 */
class BaseModel extends CacheModel
{
    // 创建时间
    const CREATED_AT = 'create_time';
    // 更新时间
    const UPDATED_AT = 'update_time';
    // // 删除时间
    // const DELETED_AT = 'delete_time';
    // 默认使用时间戳戳功能
    public $timestamps = true;
    // 人员ID
    public $userId;
    // 时间
    public $time;

    /**
     * 构造函数
     * @author zongjl
     * @date 2019/5/30
     */
    public function __construct()
    {
        // 获取用户ID
        $this->userId = JwtUtils::getUserId();
    }

    /**
     * 获取当前时间
     * @return int 时间戳
     * @author zongjl
     * @date 2019/5/30
     */
    public function freshTimestamp()
    {
        return time();
    }

    /**
     * 避免转换时间戳为时间字符串
     * @param mixed $value 时间
     * @return mixed|string|null
     * @author zongjl
     * @date 2019/5/30
     */
    public function fromDateTime($value)
    {
        return $value;
    }

    /**
     * 获取时间戳格式
     * @return string 时间戳字符串格式
     * @author zongjl
     * @date 2019/5/30
     */
    public function getDateFormat()
    {
        return 'U';
    }

    /**
     * 添加或编辑
     * @param array $data 数据源
     * @param string $error 异常错误信息
     * @param bool $is_sql 是否打印SQL
     * @return number 返回受影响行数据ID
     * @author zongjl
     * @date 2019/5/23
     */
    public function edit($data = [], &$error = '', $is_sql = false)
    {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        if ($id) {
            // 更新时间
            $data['update_time'] = time();
            // 更新人
            $data['update_user'] = $this->userId;
            // 置空添加时间
            unset($data['create_time']);
            // 置空添加人
            unset($data['create_user']);
        } else {
            // 添加时间
            $data['create_time'] = time();
            // 添加人
            $data['create_user'] = $this->userId;
        }

        // 格式化表数据
        $this->formatData($data, $id);

        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 入库处理
        if ($id) {
            // 修改数据
            $result = $this->where('id', $id)->update($data);
            // 更新ID
            $rowId = $id;
        } else {
            // 新增数据
            $result = $this->insertGetId($data);
            // 新增ID
            $rowId = $result;
        }

        if ($result !== false) {
            // 重置缓存
            if ($this->is_cache) {
                $data['id'] = $rowId;
                $this->cacheReset($rowId, $data, $id);
            }
        }
        return $rowId;
    }

    /**
     * 格式化数据
     * @param array $data 数组数据
     * @param int $id 数据记录ID
     * @param string $table 数据表
     * @return array 格式化数据
     * @author zongjl
     * @date 2019/5/24
     */
    private function formatData(&$data = [], $id = 0, $table = '')
    {
        $data_arr = [];
        $tables = $table ? explode(",", $table) : array($this->getTable());
        $item_data = [];
        foreach ($tables as $table) {
            $temp_data = [];
            $table_fields_list = $this->getTableFields($table);
            foreach ($table_fields_list as $field => $fieldInfo) {
                if ($field == "id") {
                    continue;
                }
                //对强制
                if (isset($data[$field])) {
                    if ($fieldInfo['Type'] == "int") {
                        $item_data[$field] = (int)$data[$field];
                    } else {
                        $item_data[$field] = (string)$data[$field];
                    }
                }
                if (!isset($data[$field]) && in_array($field, array('update_time', 'create_time'))) {
                    continue;
                }
                //插入数据-设置默认值
                if (!$id && !isset($data[$field])) {
                    $item_data[$field] = $fieldInfo['Default'];
                }
                if (isset($item_data[$field])) {
                    $temp_data[$field] = $item_data[$field];
                }
            }
            $data_arr[] = $temp_data;
        }
        $data = $item_data;
        return $data_arr;
    }

    /**
     * 获取数据表字段
     * @param string $table 数据表名
     * @return array 字段数组
     * @author zongjl
     * @date 2019/5/24
     */
    private function getTableFields($table = '')
    {
        $table = $table ? $table : $this->getTable();
        if (strpos($table, DB_PREFIX) === false) {
            $table = DB_PREFIX . $table;
        }
        $field_list = \DB::select("SHOW FIELDS FROM {$table}");
        $info_list = [];
        foreach ($field_list as $row) {
            // 对象转数组格式
            $item = object_array($row);
            if ((strpos($item['Type'], "int") === false) || (strpos($item['Type'], "bigint") !== false)) {
                $type = "string";
                $default = $item['Default'] ? $item['Default'] : "";
            } else {
                $type = "int";
                $default = $item['Default'] ? $item['Default'] : 0;
            }
            $info_list[$item['Field']] = array(
                'Type' => $type,
                'Default' => $default
            );
        }
        return $info_list;
    }

    /**
     * 删除数据
     * @param int $id 删除数据ID
     * @param bool $is_sql 是否打印SQL
     * @return bool 返回true或false
     * @author zongjl
     * @date 2019/5/23
     */
    public function drop($id, $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        $result = $this->where('id', $id)->update(['mark' => 0]);
        if ($result !== false && $this->is_cache) {
            // 删除成功
            $this->cacheDelete($id);
        }
        return $result;
    }

    /**
     * 查询缓存信息
     * @param int $id 查询数据ID
     * @return string 返回查询结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getInfo($id)
    {
        // 获取参数(用户提取操作人信息)
        $arg_list = func_get_args();
        $flag = isset($arg_list[0]) ? $arg_list[0] : 0;

        // 获取缓存信息
        $info = $this->getCacheFunc("info", $id);
        if ($info) {

            // 获取系统人员缓存
            $adminModel = new UserModel();
            $adminAll = $adminModel->getAll([], false, true);

            // 添加人
            if (isset($info['create_user']) && !empty($info['create_user'])) {
                $create_name = isset($adminAll[$info['create_user']]['realname']) ? $adminAll[$info['create_user']]['realname'] : "";
                $info['create_user_name'] = $create_name;
            }

            // 添加时间
            if (!empty($info['create_time'])) {
                $info['create_time'] = datetime($info['create_time'], 'Y-m-d H:i:s');
            }

            // 更新人
            if (isset($info['update_user']) && !empty($info['update_user'])) {
                $update_name = isset($adminAll[$info['update_user']]['realname']) ? $adminAll[$info['update_user']]['realname'] : "";
                $info['update_user_name'] = $update_name;
            }

            // 更新时间
            if (!empty($info['update_time'])) {
                $info['update_time'] = datetime($info['update_time'], 'Y-m-d H:i:s');
            }

            // 格式化信息(预留扩展方法,可不用)
            if (method_exists($this, 'formatInfo')) {
                $info = $this->formatInfo($info);
            }
        }
        return $info;
    }

    /**
     * 格式化数据
     * @param array $info 实体数据对象
     * @return array 返回实体对象
     * @author zongjl
     * @date 2019/5/23
     */
    public function formatInfo($info)
    {
        // 基类方法可不做任何操作，在子类重写即可
        // TODO...
        return $info;
    }

    /**
     * 查询记录总数
     * @param array $map 查询条件（默认：数组格式）
     * @param string $fields 查询字段
     * @param bool $is_sql 是否打印SQL
     * @return int 返回记录总数
     * @author zongjl
     * @date 2019/5/23
     */
    public function getCount($map = [], $fields = null, $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        if ($fields) {
            $count = $query->count($fields);
        } else {
            $count = $query->count();
        }
        return (int)$count;
    }

    /**
     * 查询某个字段的求和值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 求和字段
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getSum($map = [], $field = '', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->sum($field);
        return $result;
    }

    /**
     * 查询某个字段的最大值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字段
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getMax($map = [], $field = '', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $$query->max($field);
        return $result;
    }

    /**
     * 查询某个字段的最小值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字典
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getMin($map = [], $field = '', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->min($field);
        return $result;
    }

    /**
     * 查询某个字段的平均值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字段
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getAvg($map = [], $field = '', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->avg($field);
        return $result;
    }

    /**
     * 查询某个字段的单个值
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字段
     * @param bool $is_sql 是否打印SQL
     * @return string 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getValue($map = [], $field = 'id', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->value($field);
        return $result;
    }

    /**
     * 查询单条数据
     * @param array $map 查询条件（默认：数组）
     * @param string $field 查询字段（默认：全部）
     * @param bool $is_sql 是否打印SQL
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getOne($map = [], $field = '*', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 分析字段
        if (!is_array($field) && strpos($field, ',')) {
            $field = explode(',', $field);
        }
        // 链式操作
        $result = $query->select($field)->first();

        // 对象转数组
        return $result ? $result->toArray() : [];
    }

    /**
     * 根据记录ID获取某一行的值
     * @param int $id 记录ID
     * @param string $field 指定字段（默认：所有字段）
     * @param bool $is_sql 是否打印SQL
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getRow($id, $field = '*', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 分析字段
        if (!is_array($field) && strpos($field, ',')) {
            $field = explode(',', $field);
        }
        // 链式操作
        $result = $this->where('id', $id)->select($field)->first();

        // 对象转数组
        return $result ? $result->toArray() : [];
    }

    /**
     * 获取某一列的值
     * @param array $map 查询条件
     * @param string $field 字段
     * @param bool $is_sql 是否打印SQL
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function getColumn($map = [], $field = 'id', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 链式操作
        $result = $query->pluck($field);

        // 对象转数组
        return $result ? $result->toArray() : [];
    }

    /**
     * 根据条件查询单条缓存记录
     * @param array $map 查询条件
     * @param array $fields 查询字段
     * @param array $sort 排序
     * @param int $id 记录ID
     * @return array 结果返回值
     * @author zongjl
     * @date 2019/5/29
     */
    public function getInfoByAttr($map = [], $fields = [], $sort = [['id', 'desc']], $id = 0)
    {
        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 排除主键
        if ($id) {
            $map[] = ['id', '!=', $id];
        }

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 排序(支持多重排序)
        $query = $query->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

        // 链式操作
        $result = $query->select('id')->first();
        $result = $result ? $result->toArray() : [];

        // 查询缓存
        $data = [];
        if ($result) {
            $info = $this->getInfo($result['id']);
            if ($info && !empty($fields)) {
                // 分析字段
                if (!is_array($fields) && strpos($fields, ',')) {
                    $fields = explode(',', $fields);
                }
                foreach ($fields as $val) {
                    $data[trim($val)] = $info[trim($val)];
                }
                unset($info);
            } else {
                $data = $info;
            }
        }
        return $data;
    }

    /**
     * 获取数据表
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function getTablesList()
    {
        $tables = [];
        $database = strtolower(env('DB_DATABASE'));
        $sql = 'SHOW TABLES';
        $list = \DB::select($sql);
        // 对象转数组
        $data = object_array($list);
        foreach ($data as $v) {
            $tables[] = $v["Tables_in_{$database}"];
        }
        return $tables;
    }

    /**
     * 检查表是否存在
     * @param string $table 数据表名
     * @return bool 返回结果：true存在,false不存在
     * @author zongjl
     * @date 2019/5/29
     */
    public function tableExists($table)
    {
        if (strpos($table, DB_PREFIX) === false) {
            $table = DB_PREFIX . $table;
        }
        $tables = $this->getTablesList();
        return in_array($table, $tables) ? true : false;
    }

    /**
     * 删除数据表
     * @param string $table 数据表名
     * @return mixed 结果返回值
     * @author zongjl
     * @date 2019/5/29
     */
    public function dropTable($table)
    {
        if (strpos($table, DB_PREFIX) === false) {
            $table = DB_PREFIX . $table;
        }
        return \DB::statement("DROP TABLE {$table}");
    }

    /**
     * 获取表字段
     * @param string $table 数据表名
     * @return array 字段数组
     * @author zongjl
     * @date 2019/5/29
     */
    public function getFieldsList($table)
    {
        if (strpos($table, DB_PREFIX) === false) {
            $table = DB_PREFIX . $table;
        }
        $fields = [];
        $list = \DB::select("SHOW COLUMNS FROM {$table}");
        // 对象转数组
        $data = object_array($list);
        foreach ($data as $v) {
            $fields[$v['Field']] = $v['Type'];
        }
        return $fields;
    }

    /**
     * 检查字段是否存在
     * @param string $table 数据表名
     * @param string $field 字段名
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/29
     */
    public function fieldExists($table, $field)
    {
        $fields = $this->getFieldsList($table);
        return array_key_exists($field, $fields);
    }

    /**
     * 插入数据(不存在缓存操作,请慎用)
     * @param array $data 数据源
     * @param bool $get_id 是否返回插入主键ID：true返回、false不返回
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function doInsert($data, $get_id = true)
    {
        if ($get_id) {
            // 插入数据并返回主键
            return $this->insertGetId($data);
        } else {
            // 返回影响数据的条数，没修改任何数据返回 0
            return $this->insert($data);
        }
    }

    /**
     * 更新数据(不存在缓存操作,请慎用)
     * @param array $data 数据源
     * @param array $where 更新条件
     * @param bool $is_sql
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function doUpdate($data, $where, $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $where);

        return $query->update($data);
    }

    /**
     * 删除数据(不存在缓存操作,请慎用)
     * @param array $where 查询条件
     * @param bool $is_sql 是否打印SQL
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/29
     */
    public function doDelete($where, $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $where);

        return $query->delete();
    }

    /**
     * 批量插入数据
     * @param array $data 数据源
     * @param bool $is_cache 是否设置缓存：true设置,false不设置
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/30
     */
    public function insertAll($data, $is_cache = true)
    {
        if (!is_array($data)) {
            return false;
        }
        if ($is_cache) {
            // 插入数据并设置缓存
            $num = 0;
            foreach ($data as $val) {
                $result = $this->edit($val);
                if ($result) {
                    $num++;
                }
            }
            return $num ? true : false;
        } else {
            // 插入数据不设置缓存
            return $this->insert($data);
        }
        return false;
    }

    /**
     * 批量更新数据
     * @param array $data 数据源(备注，需要更新的数据对象中必须包含有效主键)
     * @param bool $is_cache 是否设置缓存：true设置,false不设置
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/30
     */
    public function saveAll($data, $is_cache = true)
    {
        if (!is_array($data)) {
            return false;
        }

        $num = 0;
        foreach ($data as $val) {
            if (!isset($val['id']) || empty($val['id'])) {
                continue;
            }
            if ($is_cache) {
                // 更新数据并设置缓存
                $result = $this->edit($val);
            } else {
                // 更新数据不设置缓存
                $id = $val['id'];
                unset($val['id']);
                $result = $this->where('id', $id)->update($val);
            }
            if ($result) {
                $num++;
            }
        }
        return $num ? true : false;
    }

    /**
     * 批量删除
     * @param array $data 删除记录ID(支持传入数组和逗号分隔ID字符串)
     * @param bool $is_force 是否物理删除,true物理删除false软删除
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/30
     */
    public function deleteAll($data, $is_force = false)
    {
        if (empty($data)) {
            return false;
        }
        if (!is_array($data)) {
            $data = explode(',', $data);
        }

        $num = 0;
        foreach ($data as $val) {
            if ($is_force) {
                // 物理删除
                $result = $this->where('id', $val)->delete();
                if ($result) {
                    $this->cacheDelete($val);
                }
            } else {
                // 软删除
                $result = $this->drop($val);
            }
            if ($result) {
                $num++;
            }
        }
        return $num ? true : false;
    }

    /**
     * 获取数据列表【根据业务场景需要，封装的获取列表数据的常用方法】
     * @param array $map 查询条件
     * @param array $sort 排序（默认：id asc）
     * @param string $limit 限制条数
     * @param bool $is_sql 是否打印SQL
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getList($map = [], $sort = [['id', 'asc']], $limit = '', $is_sql = false)
    {
        // 注册打印SQL监听事件
        $this->getLastSql($is_sql);

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($this, $map);

        // 数据分页设置
        if ($limit) {
            list($offset, $page_size) = explode(',', $limit);
            $query = $query->offset($offset)->limit($page_size);
        }

        // 排序(支持多重排序)
        $query = $query->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

        // 查询数据并将对象转数组
        $result = $query->select('id')->get();
        $result = $result ? $result->toArray() : [];

        $list = [];
        if ($result) {
            foreach ($result as $val) {
                $info = $this->getInfo($val['id']);
                if (!$info) {
                    continue;
                }
                $list[] = $info;
            }
        }
        return $list;
    }

    /**
     * 获取数据列表
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/27
     */
    public function getData()
    {
        // 获取参数
        $arg_list = func_get_args();

        // 查询参数
        $map = isset($arg_list[0]['query']) ? $arg_list[0]['query'] : [];
        // 排序
        $sort = isset($arg_list[0]['sort']) ? $arg_list[0]['sort'] : [['id', 'desc']];
        // 获取条数
        $limit = isset($arg_list[0]['limit']) ? $arg_list[0]['limit'] : '';
        // 回调方法名
        $func = isset($arg_list[1]) ? $arg_list[1] : "Short";
        // 自定义MODEL
        $model = isset($arg_list[2]) ? $arg_list[2] : $this;

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 闭包查询条件格式化
        $query = $this->formatQuery($model, $map);

        // 排序(支持多重排序)
        $query = $query->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

        // 查询数据源
        if ($limit) {
            list($offset, $page_size) = explode(',', $limit);
            $query->offset($offset)->limit($page_size);
        } else {
            // TODO...
        }

        // 查询数据并转为数组
        $result = $query->select('id')->get();
        $result = $result ? $result->toArray() : [];
        $list = [];
        if (is_array($result)) {
            foreach ($result as $val) {
                $info = $model->getInfo($val['id']);
                if (!$info) {
                    continue;
                }
                if (is_object($func)) {
                    // 方法函数
                    $data = $func($info);
                } else {
                    // 直接返回
                    $data = $info;
                }
                $list[] = $data;
            }
        }
        return $list;
    }

    /**
     * 获取数据列表
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/27
     */
    public function pageData()
    {
        // 获取参数
        $arg_list = func_get_args();
        // 查询参数
        $map = isset($arg_list[0]['query']) ? $arg_list[0]['query'] : [];
        // 排序
        $sort = isset($arg_list[0]['sort']) ? $arg_list[0]['sort'] : [['id', 'desc']];
        // 页码
        $page = isset($arg_list[0]['page']) ? $arg_list[0]['page'] : 1;
        // 每页数
        $perpage = isset($arg_list[0]['perpage']) ? $arg_list[0]['perpage'] : 20;
        // 回调方法名
        $func = isset($arg_list[1]) ? $arg_list[1] : "Short";
        // 自定义MODEL
        $model = isset($arg_list[2]) ? $arg_list[2] : $this;

        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 分页设置
        $start = ($page - 1) * $perpage;
        $limit = "{$start},{$perpage}";

        // 闭包查询条件格式化
        $query = $this->formatQuery($model, $map);

        // 查询总数
        $count = $query->count();

        // 排序(支持多重排序)
        $query = $query->when($sort, function ($query, $sort) {
            foreach ($sort as $v) {
                $query->orderBy($v[0], $v[1]);
            }
        });

        // 分页设置
        list($offset, $page_size) = explode(',', $limit);
        $result = $query->offset($offset)->limit($page_size)->select('id')->get();
        $result = $result ? $result->toArray() : [];

        $list = [];
        if (is_array($result)) {
            foreach ($result as $val) {
                $info = $model->getInfo($val['id']);
                if (!$info) {
                    continue;
                }
                if (is_object($func)) {
                    //方法函数
                    $data = $func($info);
                } else {
                    // 直接返回
                    $data = $info;
                }
                $list[] = $data;
            }
        }

        // 返回结果
        $result = array(
            'count' => $count,
            'perpage' => $perpage,
            'page' => $page,
            'list' => $list,
        );
        return $result;
    }

    /**
     * 格式化查询条件
     * @param $model 模型
     * @param array $map 查询条件
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/30
     */
    public function formatQuery($model, $map)
    {
        $query = $model->where(function ($query) use ($map) {
            foreach ($map as $v) {
                if ($v instanceof \Closure) {
                    $query = $query->where($v);
                    continue;
                }
                // 判断是否是键值对类型
                if (key($v) !== 0) {
                    $key = key($v);
                    $val = $v[$key];
                    $v = [$key, is_array($val) ? 'in' : '=', $val];
                }
                switch ($v[1]) {
                    case 'like':
                        // like查询
                        if (strpos($v[0], '|') !== false) {
                            $query->where(function ($query) use ($v) {
                                $item = explode('|', $v[0]);
                                foreach ($item as $vo) {
                                    $query->orWhere($vo, $v[1], $v[2]);
                                }
                            });
                        } else {
                            $query->where($v[0], $v[1], $v[2]);
                        }
                        break;
                    case 'in':
                        // in查询
                        if (!is_array($v[2])) {
                            $v[2] = explode(',', $v[2]);
                        }
                        $query->whereIn($v[0], $v[2]);
                        break;
                    case 'not in':
                        // not in查询
                        if (!is_array($v[2])) {
                            $v[2] = explode(',', $v[2]);
                        }
                        $query->whereNotIn($v[0], $v[2]);
                        break;
                    case 'between':
                        // between查询
                        if (!is_array($v[2])) {
                            $v[2] = explode(',', $v[2]);
                        }
                        $query->whereBetween($v[0], $v[2]);
                        break;
                    case 'not between':
                        // not between查询
                        if (!is_array($v[2])) {
                            $v[2] = explode(',', $v[2]);
                        }
                        $query->whereNotBetween($v[0], $v[2]);
                        break;
                    case 'null':
                        // null查询
                        $query->whereNull($v[0]);
                        break;
                    case "not null":
                        // not null查询
                        $query->whereNotNull($v[0]);
                        break;
                    case "or":
                        // or查询
                        //格式：or (status=1 and status=2)
                        $where = $v[0];
                        $query->orWhere(function ($query) use ($where) {
                            // 递归解析查询条件
                            $this->formatQuery($query, $where);
                        });
                        break;
                    case 'xor':
                        // xor查询
                        // 格式：and (status=1 or status=2)
                        $where = $v[0];
                        $query->where(function ($query) use ($where) {
                            foreach ($where as $w) {
                                $query->orWhere(function ($query) use ($w) {
                                    // 递归解析查询条件
                                    $this->formatQuery($query, [$w]);
                                });
                            }
                        });
                        break;
                    default:
                        // 常规查询
                        if (count($v) == 2) {
                            $query->where($v[0], '=', $v[1]);
                        } else {
                            $query->where($v[0], $v[1], $v[2]);
                        }
                        break;
                }
            }
        });
        return $query;
    }

    /**
     * 添加打印SQL语句监听事件
     * @param bool $is_sql 是否打印SQL
     * @author zongjl
     * @date 2019/5/29
     */
    public function getLastSql($is_sql = false)
    {
        if ($is_sql) {
            \DB::listen(function ($query) {
                $bindings = $query->bindings;
                $sql = $query->sql;
                foreach ($bindings as $replace) {
                    $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }
                echo $sql;
            });
        }
    }

    /**
     * 开启事务
     * @author zongjl
     * @date 2019/5/30
     */
    public function startTrans()
    {
        // 事务-缓存相关处理
        $GLOBALS['trans'] = true;
        $transId = uniqid("trans_");
        $GLOBALS['trans_id'] = $transId;
        $GLOBALS['trans_keys'] = [];
        $info = debug_backtrace();
        $this->setCache($transId, $info[0]);

        // 开启事务
        \DB::beginTransaction();
    }

    /**
     * 事务回滚
     * @author zongjl
     * @date 2019/5/30
     */
    public function rollBack()
    {
        // 事务回滚
        \DB::rollBack();

        // 回滚缓存处理
        foreach ($GLOBALS['trans_keys'] as $key) {
            $this->deleteCache($key);
        }
        $this->deleteCache($GLOBALS['trans_id']);
        $GLOBALS['trans'] = false;
        $GLOBALS['trans_keys'] = [];
    }

    /**
     * 提交事务
     * @author zongjl
     * @date 2019/5/30
     */
    public function commit()
    {
        // 提交事务
        \DB::commit();

        // 事务缓存同步删除
        $GLOBALS['trans'] = false;
        $GLOBALS['trans_keys'] = [];
        $this->deleteCache($GLOBALS['trans_id']);
    }

    /**
     * 开启执行日志
     * @author zongjl
     * @date 2019/5/31
     */
    public function beginSQLLog()
    {
        \DB::connection()->enableQueryLog();
    }

    /**
     * 结束日志并打印
     * @author zongjl
     * @date 2019/5/30
     */
    public function endSQLLog()
    {
        // 获取查询语句、参数和执行时间
        $result = \DB::getLastSql();
        if ($result) {
            foreach ($result as &$val) {
                $bindings = $val['bindings'];
                $sql = $val['query'];
                foreach ($bindings as $replace) {
                    $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                    $val['query'] = preg_replace('/\?/', $value, $sql, 1);
                }
            }
        }
        print_r($result);
        exit;
    }
}
