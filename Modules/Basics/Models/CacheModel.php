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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * 缓存模型
 * @author zongjl
 * @date 2019/5/23
 * Class CacheModel
 * @package App\Models
 */
class CacheModel extends Model
{
    // 是否启用缓存
    protected $is_cache = true;

    /**
     * 重置缓存
     * @param int $id 记录ID
     * @param array $data 数据源
     * @param bool $is_edit 是否编辑
     * @return bool 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function cacheReset($id, $data = [], $is_edit = false)
    {
        if (!$data) {
            $this->resetCacheFunc('info', $id);
        }
        $info = [];
        if ($is_edit) {
            $info = $this->getCacheFunc("info", $id);
        }
        if (is_array($data)) {
            $info = array_merge($info, $data);
        } else {
            $info = $data;
        }
        $cache_key = $this->getCacheKey('info', $id);
        $result = $this->setCache($cache_key, $info);
        return $result;
    }

    /**
     * 删除缓存
     * @param int $id 删除缓存ID
     * @return bool 返回true或false
     * @author zongjl
     * @date 2019/5/23
     */
    public function cacheDelete($id)
    {
        $cache_key = $this->getCacheKey("info", $id);
        $result = $this->deleteCache($cache_key);
        return $result;
    }

    /**
     * 设置单条数据缓存
     * @param int $id 记录ID
     * @return array 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function cacheInfo($id)
    {
        if (!$id) {
            return false;
        }
        $data = $this->find((int)$id);
        return $data ? $data->toArray() : [];
    }

    /**
     * 获取缓存KEY
     * @return string 缓存KEY
     * @author zongjl
     * @date 2019/5/23
     */
    public function getCacheKey()
    {
        $arg_list = func_get_args();
        if ($this->table) {
            array_unshift($arg_list, $this->table);
        }
        foreach ($arg_list as $key => $val) {
            if (is_array($val)) {
                unset($arg_list[$key]);
            }
        }
        $cache_key = implode("_", $arg_list);
        return $cache_key;
    }

    /**
     * 设置缓存
     * @param string $cache_key 缓存KEY
     * @param array $data 缓存数据
     * @param int $ttl 过期时间
     * @return bool 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function setCache($cache_key, $data, $ttl = 0)
    {
        if (isset($GLOBALS['trans']) && $GLOBALS['trans'] === true) {
            $GLOBALS['trans_keys'][] = $cache_key;
        }

        // 不设置缓存，直接返回
        if (!$this->is_cache) {
            return true;
        }
        if (!$data) {
            return Cache::put($cache_key, null, $ttl);
        }
        $isGzcompress = gzcompress(json_encode($data));
        if ($isGzcompress) {
            $result = Cache::put($cache_key, $isGzcompress);
        }
        return $result;
    }

    /**
     * 获取缓存
     * @param string $cache_key 缓存KEY
     * @return mixed 返回缓存数据
     * @author zongjl
     * @date 2019/5/23
     */
    public function getCache($cache_key)
    {
        $data = Cache::get($cache_key);
        if ($data) {
            $data = json_decode(gzuncompress($data), true);
        }
        return $data;
    }

    /**
     * 删除缓存
     * @param string $cache_key 缓存KEY
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/23
     */
    public function deleteCache($cache_key)
    {
        // 判断缓存键值是否存在,存在则删除
        if (Cache::has($cache_key)) {
            return Cache::forget($cache_key);
        }
        return false;
    }

    /**
     * 设置缓存函数
     * @param string $funcName 方法名
     * @param string $id 缓存数据ID
     * @return bool 返回结果true或false
     * @author zongjl
     * @date 2019/5/23
     */
    public function resetCacheFunc($funcName, $id = '')
    {
        // 获取缓存KEY
        $cache_key = $this->getCacheKey($funcName, $id);
        // 删除缓存
        $result = $this->deleteCache($cache_key);

        // 设置缓存
        $arg_list = func_get_args();
        if ($this->table) {
            array_shift($arg_list);
        }
        $act = "cache" . ucfirst($funcName);
        $data = call_user_func_array(array($this, $act), $arg_list);
        return $this->setCache($cache_key, $data);
    }

    /**
     * 获取缓存函数
     * @param string $funcName 方法名
     * @param string $id 缓存数据ID
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/5/23
     */
    public function getCacheFunc($funcName, $id = '')
    {
        $cache_key = $this->getCacheKey($funcName, $id);
        $data = $this->getCache($cache_key);
        if (!$data) {
            $arg_list = func_get_args();
            if ($this->table) {
                array_shift($arg_list);
            }
            $act = "cache" . ucfirst($funcName);
            $data = call_user_func_array(array($this, $act), $arg_list);
            $this->setCache($cache_key, $data);
        }
        return $data;
    }

    /**
     * 获取整表缓存
     * @param array $map 查询条件
     * @param bool $is_pri 是否只缓存主键true或false
     * @param bool $pri_key 是否以主键作为键值true或false
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/6/5
     */
    public function getAll($map = [], $is_pri = false, $pri_key = false)
    {
        $list = $this->getCacheFunc('all', $map, $is_pri, $pri_key);
        return $list;
    }

    /**
     * 设置整表缓存
     * @param array $map 查询条件
     * @param bool $is_pri 是否只缓存主键true或false
     * @param bool $pri_key 是否以主键作为键值true或false
     * @return mixed 返回结果
     * @author zongjl
     * @date 2019/6/5
     */
    public function cacheAll($map = [], $is_pri = false, $pri_key = false)
    {
        // 必备查询条件
        $map[] = ['mark', '=', 1];

        // 格式化查询条件
        if (method_exists($this, 'formatQuery')) {
            $query = $this->formatQuery($this, $map);
        }

        // 是否缓存主键
        if ($is_pri) {
            if (is_array($is_pri)) {
                // 字段数组
                $query->select($is_pri);
            } elseif (is_string($is_pri)) {
                // 字段字符串
                $fields = explode(',', $is_pri);
                $query->select($fields);
            } else {
                // 默认主键ID
                $query->select('id');
            }
        }

        // 查询数据并转数组
        $list = $query->get()->toArray();

        // 设置主键ID为数组键值
        if ($pri_key) {
            $list = array_column($list, null, 'id');
        }

        return $list;
    }

    /**
     * 删除整体缓存
     * @author 牧羊人
     * @since 2021/6/9
     */
    public function cacheDAll()
    {
        $cache_key = $this->getCacheKey("all");
        $this->deleteCache($cache_key);
    }

    /**
     * 重置全表缓存
     * @param array $map 查询条件
     * @param bool $is_pri 是否只缓存主键true或false
     * @param bool $pri_key 是否以主键作为键值true或false
     * @return bool 返回结果true(重置成功)或false(重置失败)
     * @author zongjl
     * @date 2019/6/5
     */
    public function cacheResetAll($map = [], $is_pri = false, $pri_key = false)
    {
        return $this->resetCacheFunc('all', $map, $is_pri, $pri_key);
    }
}
