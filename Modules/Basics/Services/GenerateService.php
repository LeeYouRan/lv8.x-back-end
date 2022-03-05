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

use Modules\Basics\Models\MenuModel;
use Illuminate\Support\Facades\DB;

/**
 * 代码生成器-服务类
 * @author 牧羊人
 * @since 2020/11/12
 * Class GenerateService
 * @package App\Services
 */
class GenerateService extends BaseService
{

    /**
     * 获取数据表
     * @return array
     * @since 2020/11/12
     * @author 牧羊人
     */
    public function getList()
    {
        // 查询SQL
        $sql = "SHOW TABLE STATUS WHERE 1";
        // 请求参数
        $param = request()->all();
        // 表名称
        $name = getter($param, "name");
        if ($name) {
            $sql .= " AND NAME like \"%{$name}%\" ";
        }
        // 表描述
        $comment = getter($param, "comment");
        if ($comment) {
            $sql .= " AND COMMENT like \"%{$comment}%\" ";
        }
        $list = DB::select($sql);
        $list = json_decode(json_encode($list), true);
        $list = array_map('array_change_key_case', $list);

        return $message = array(
            "msg" => '操作成功',
            "code" => 0,
            "data" => $list,
            "count" => count($list),
        );
    }

    /**
     * 一键生成模块文件
     * @param $param 参数
     * @return array
     * @author 牧羊人
     * @since 2021/10/23
     */
    public function generate($param)
    {
        // 数据表名
        $name = getter($param, "name");
        if (!$name) {
            return message("数据表名称不能为空", false);
        }
        // 数据表名称
        $tableName = str_replace(DB_PREFIX, null, $name);
        // 模型名称
        $moduleName = str_replace(' ', null, ucwords(strtolower(str_replace('_', ' ', $tableName))));
        // 控制器名称
        $controllerName = ucfirst(strtolower(str_replace('_', '', $tableName)));

        // 数据表描述
        $comment = getter($param, "comment");
        if (!$comment) {
            return message("数据表名称不能为空", false);
        }
        // 设置表名
        $menuName = $comment;
        // 去除表描述中的`表`
        if (strpos($comment, "表") !== false) {
            $comment = str_replace("表", null, $comment);
            $menuName = $comment;
        }
        // 去除表描述中的`管理`
        if (strpos($comment, "管理") !== false) {
            $comment = str_replace("管理", null, $comment);
            $menuName = $comment;
        }
        // 作者花名
        $author = "牧羊人";

        // 生成模型
        $this->generateModel($author, $moduleName, $comment, $tableName);
        // 生成服务类
        $this->generateService($author, $moduleName, $comment, $tableName);
        // 生成控制器
        $this->generateController($author, $controllerName, $comment, $tableName);
        // 生成列表文件
        $this->generateVueIndex($comment, $moduleName, $tableName);
        // 生成菜单
        $this->generateMenu(strtolower(str_replace('_', '', $tableName)), $menuName);
        // 生成路由
        $this->generateRoute($author, $controllerName, $comment, $tableName);
        return message("模块生成成功");
    }

    /**
     * 生成模型
     * @param $author 作者
     * @param $moduleName 模块名
     * @param $moduleTitle 模块标题
     * @param $tableName 数据表名
     * @author 牧羊人
     * @since 2020/11/12
     */
    public function generateModel($author, $moduleName, $moduleTitle, $tableName)
    {
        // 判断是否有图片
        $moduleImage = false;
        // 获取数据列表
        $columnList = $this->getColumnList(DB_PREFIX . "{$tableName}");
        if ($columnList) {
            foreach ($columnList as &$val) {
                // 图片字段处理
                if (strpos($val['columnName'], "cover") !== false ||
                    strpos($val['columnName'], "avatar") !== false ||
                    strpos($val['columnName'], "image") !== false ||
                    strpos($val['columnName'], "logo") !== false ||
                    strpos($val['columnName'], "pic") !== false) {
                    $val['columnImage'] = true;
                    $moduleImage = true;
                }
            }
        }
        // 参数
        $param = [
            'author' => $author,
            'since' => date('Y/m/d', time()),
            'moduleName' => $moduleName,
            'moduleTitle' => $moduleTitle,
            'tableName' => $tableName,
            'columnList' => $columnList,
            'moduleImage' => $moduleImage,
        ];
        // 存储目录
        $FILE_PATH = app_path() . '/Models';
        if (!is_dir($FILE_PATH)) {
            // 创建目录并赋予权限
            mkdir($FILE_PATH, 0777, true);
        }
        // 文件名
        $filename = $FILE_PATH . "/{$moduleName}Model.php";
        // 拆解参数
        extract($param);
        // 开启缓冲区
        ob_start();
        // 引入模板文件
        require(resource_path() . '/views/templates/model.blade.php');
        // 获取缓冲区内容
        $out = ob_get_clean();
        // 打开文件
        $f = fopen($filename, 'w');
        // 写入内容
        fwrite($f, "<?php " . $out);
        // 关闭
        fclose($f);
    }

    /**
     * 生成服务类
     * @param $author 作者
     * @param $moduleName 模块名
     * @param $moduleTitle 模块标题
     * @param $tableName 数据表
     * @author 牧羊人
     * @since 2020/11/12
     */
    public function generateService($author, $moduleName, $moduleTitle, $tableName)
    {
        // 判断是否有图片
        $moduleImage = false;
        // 查询条件
        $queryList = [];
        // 获取数据列表
        $columnList = $this->getColumnList(DB_PREFIX . "{$tableName}");
        if ($columnList) {
            foreach ($columnList as &$val) {
                // 图片字段处理
                if (strpos($val['columnName'], "cover") !== false ||
                    strpos($val['columnName'], "avatar") !== false ||
                    strpos($val['columnName'], "image") !== false ||
                    strpos($val['columnName'], "logo") !== false ||
                    strpos($val['columnName'], "pic") !== false) {
                    $val['columnImage'] = true;
                    $moduleImage = true;
                }
                // 下拉筛选
                if (isset($val['columnValue'])) {
                    $queryList[] = $val;
                }
                // 名称
                if ($val['columnName'] == "name") {
                    $queryList[] = $val;
                }
                // 标题
                if ($val['columnName'] == "title") {
                    $queryList[] = $val;
                }
            }
        }

        // 参数
        $param = [
            'author' => $author,
            'since' => date('Y/m/d', time()),
            'moduleName' => $moduleName,
            'moduleTitle' => $moduleTitle,
            'columnList' => $columnList,
            'moduleImage' => $moduleImage,
            'queryList' => $queryList,
        ];
        // 存储目录
        $FILE_PATH = app_path() . '/Services/';
        if (!is_dir($FILE_PATH)) {
            // 创建目录并赋予权限
            mkdir($FILE_PATH, 0777, true);
        }
        // 文件名
        $filename = $FILE_PATH . "/{$moduleName}Service.php";
        // 拆解参数
        extract($param);
        // 开启缓冲区
        ob_start();
        // 引入模板文件
        require(resource_path() . '/views/templates/service.blade.php');
        // 获取缓冲区内容
        $out = ob_get_clean();
        // 打开文件
        $f = fopen($filename, 'w');
        // 写入内容
        fwrite($f, "<?php " . $out);
        // 关闭
        fclose($f);
    }

    /**
     * 生成控制器
     * @param $author 作者
     * @param $moduleName 模块名
     * @param $moduleTitle 模块标题
     * @param $tableName 数据表名
     * @author 牧羊人
     * @since 2020/11/12
     */
    public function generateController($author, $moduleName, $moduleTitle, $tableName)
    {
        // 获取数据列表
        $columnList = $this->getColumnList(DB_PREFIX . "{$tableName}");
        // 参数
        $param = [
            'author' => $author,
            'since' => date('Y/m/d', time()),
            'moduleName' => $moduleName,
            'moduleTitle' => $moduleTitle,
            'columnList' => $columnList,
        ];
        // 存储目录
        $FILE_PATH = app_path() . '\Http\Controllers';
        if (!is_dir($FILE_PATH)) {
            // 创建目录并赋予权限
            mkdir($FILE_PATH, 0777, true);
        }
        // 文件名
        $filename = $FILE_PATH . "/{$param['moduleName']}Controller.php";
        // 拆解参数
        extract($param);
        // 开启缓冲区
        ob_start();
        // 引入模板文件
        require(resource_path() . '/views/templates/controller.blade.php');
        // 获取缓冲区内容
        $out = ob_get_clean();
        // 打开文件
        $f = fopen($filename, 'w');
        // 写入内容
        fwrite($f, "<?php " . $out);
        // 关闭
        fclose($f);
    }

    /**
     * 生成列表文件
     * @param $moduleTitle 模块标题
     * @param $tableName 数据表名
     * @author 牧羊人
     * @since 2020/7/15
     */
    public function generateVueIndex($moduleTitle, $moduleName, $tableName)
    {
        // 获取数据列表
        $columnList = $this->getColumnList(DB_PREFIX . "{$tableName}");
        $queryList = [];
        if ($columnList) {
            foreach ($columnList as $val) {
                // 下拉筛选
                if (isset($val['columnValue'])) {
                    $queryList[] = $val;
                }
                // 名称
                if ($val['columnName'] == "name") {
                    $queryList[] = $val;
                }
                // 标题
                if ($val['columnName'] == "title") {
                    $queryList[] = $val;
                }
            }
        }
        // 获取编辑表单数据源
        // 剔除非表单呈现字段
        $arrayList = [];
        $tempList = [];
        $rowList = [];
        $columnSplit = false;
        if ($columnList) {
            foreach ($columnList as $val) {
                // 记录ID
                if ($val['columnName'] == "id") {
                    continue;
                }
                // 创建人
                if ($val['columnName'] == "create_user") {
                    continue;
                }
                // 创建时间
                if ($val['columnName'] == "create_time") {
                    continue;
                }
                // 更新人
                if ($val['columnName'] == "update_user") {
                    continue;
                }
                // 更新时间
                if ($val['columnName'] == "update_time") {
                    continue;
                }
                // 有效标识
                if ($val['columnName'] == "mark") {
                    continue;
                }
                // 图片字段处理
                if (strpos($val['columnName'], "cover") !== false ||
                    strpos($val['columnName'], "avatar") !== false ||
                    strpos($val['columnName'], "image") !== false ||
                    strpos($val['columnName'], "logo") !== false ||
                    strpos($val['columnName'], "pic") !== false) {
                    $val['columnImage'] = true;
                    $tempList[] = $val;
                    continue;
                }
                // 多行文本输入框
                if (strpos($val['columnName'], "note") !== false ||
                    strpos($val['columnName'], "content") !== false ||
                    strpos($val['columnName'], "description") !== false ||
                    strpos($val['columnName'], "intro") !== false) {
                    $val['columnRow'] = true;
                    $rowList[] = $val;
                    continue;
                }
                // 由于目前时间字段采用int类型，所以这里根据字段描述模糊确定是否是时间选择
                if (strpos($val['columnComment'], "时间") !== false) {
                    $val['dataType'] = 'datetime';
                } elseif (strpos($val['columnComment'], "日期") !== false) {
                    $val['dataType'] = 'date';
                }

                // 图片字段处理
                if (strpos($val['columnName'], "cover") !== false ||
                    strpos($val['columnName'], "avatar") !== false ||
                    strpos($val['columnName'], "image") !== false ||
                    strpos($val['columnName'], "logo") !== false ||
                    strpos($val['columnName'], "pic") !== false) {
                    $val['columnImage'] = true;
                }
                $arrayList[] = $val;
            }
        }
        if (count($arrayList) + count($tempList) + count($rowList) > 105) {
            $dataList = [];
            // 分两个一组
            $dataList = array_chunk($arrayList, 2);
            // 图片
            if (count($tempList) > 0) {
                array_unshift($dataList, $tempList);
            }
            // 多行文本
            if (count($rowList) > 0) {
                foreach ($rowList as $val) {
                    $dataList[][] = $val;
                }
            }
            $columnList = $dataList;
            $columnSplit = true;
        } else {
            $dataList = $arrayList;
            // 图片
            foreach ($tempList as $val) {
                array_unshift($dataList, $val);
            }
            // 多行文本
            if (count($rowList) > 0) {
                foreach ($rowList as $val) {
                    $dataList[] = $val;
                }
            }
            $columnList = $dataList;
            $columnSplit = false;
        }

        // 参数
        $param = [
            'entityName' => $moduleName,
            'moduleName' => strtolower($moduleName),
            'moduleTitle' => $moduleTitle,
            'queryList' => $queryList,
            'columnList' => $columnList,
        ];
        // 存储目录
        if (strpos($tableName, "_") !== false) {
            $tableName = str_replace('_', null, $tableName);
        }
        $FILE_PATH = ROOT_PATH . '/evui/src/views/tool/example/' . strtolower($tableName);
        if (!is_dir($FILE_PATH)) {
            // 创建目录并赋予权限
            mkdir($FILE_PATH, 0777, true);
        }
        // 文件名
        $filename = $FILE_PATH . "/index.vue";
        // 拆解参数
        extract($param);
        // 开启缓冲区
        ob_start();
        // 引入模板文件
        require(resource_path() . '/views/templates/index.blade.php');
        // 获取缓冲区内容
        $out = ob_get_clean();
        // 打开文件
        $f = fopen($filename, 'w');
        // 写入内容
        fwrite($f, $out);
        // 关闭
        fclose($f);

        // 文件名
        $filename2 = $FILE_PATH . "/" . strtolower($tableName) . "-edit.vue";
        // 拆解参数
        extract($param);
        // 开启缓冲区
        ob_start();
        // 引入模板文件
        require(resource_path() . '/views/templates/edit.blade.php');
        // 获取缓冲区内容
        $out2 = ob_get_clean();
        // 打开文件
        $f2 = fopen($filename2, 'w');
        // 写入内容
        fwrite($f2, $out2);
        // 关闭
        fclose($f2);
    }

    /**
     * 生成字段列表
     * @param $tableName 数据表名
     * @return array
     * @author 牧羊人
     * @since 2020/11/12
     */
    public function getColumnList($tableName)
    {
        // 获取表列字段信息
        $columnList = DB::select("SELECT COLUMN_NAME,COLUMN_DEFAULT,DATA_TYPE,COLUMN_TYPE,COLUMN_COMMENT FROM information_schema.`COLUMNS` where TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '{$tableName}'");
        $columnList = json_decode(json_encode($columnList), true);
        $fields = [];
        if ($columnList) {
            foreach ($columnList as $val) {
                $column = [];
                // 列名称
                $column['columnName'] = $val['COLUMN_NAME'];
                // 列默认值
                $column['columnDefault'] = $val['COLUMN_DEFAULT'];
                // 数据类型
                $column['dataType'] = $val['DATA_TYPE'];
                // 列描述
                if (strpos($val['COLUMN_COMMENT'], '：') !== false) {
                    $item = explode("：", $val['COLUMN_COMMENT']);
                    $column['columnComment'] = $item[0];

                    // 拆解字段描述
                    $param = explode(" ", $item[1]);
                    $columnValue = [];
                    $columnValueList = [];
                    $typeList = ["", "success", "warning", "danger", "info", "", "success", "warning", "danger", "info", "", "success", "warning", "danger", "info"];
                    foreach ($param as $ko => $vo) {
                        // 键值
                        $key = preg_replace('/[^0-9]/', '', $vo);
                        // 键值内容
                        $value = str_replace($key, null, $vo);
//                        $columnValue[] = "{$key}={$value}";
                        $columnValue[] = [
                            'value' => $key,
                            'name' => $value,
                            'type' => $typeList[$ko],
                        ];
                        $columnValueList[] = $value;
                    }
                    $column['columnValue'] = $columnValue;//implode(',', $columnValue);
                    if ($val['COLUMN_NAME'] == "status" || substr($val['COLUMN_NAME'], 0, 3) == "is_") {
                        $column['columnSwitch'] = true;
                        $column['columnSwitchValue'] = implode('|', $columnValueList);
                        if ($val['COLUMN_NAME'] == "status") {
                            $column['columnSwitchName'] = "status";
                        } else {
                            $column['columnSwitchName'] = 'set' . str_replace(' ', null, ucwords(strtolower(str_replace('_', ' ', $val['COLUMN_NAME']))));
                        }
                    } else {
                        $column['columnSwitch'] = false;
                    }
                } else {
                    $column['columnComment'] = $val['COLUMN_COMMENT'];
                }
                $fields[] = $column;
            }
        }
        return $fields;
    }

    /**
     * 生成模块菜单
     * @param $moduleName 模块名称
     * @param $moduleTitle 模块标题
     * @author 牧羊人
     * @since 2020/11/12
     */
    public function generateMenu($moduleName, $moduleTitle)
    {
        // 查询已存在的菜单
        $menuModel = new MenuModel();
        $info = $menuModel->getOne([
            ['permission', '=', "sys:{$moduleName}:view"],
        ]);
        $data = [
            'id' => isset($info['id']) ? intval($info['id']) : 0,
            'title' => $moduleTitle,
            'icon' => 'el-icon-house',
            'path' => "/tool/example/{$moduleName}",
            'component' => "/tool/example/{$moduleName}",
            'pid' => 164,
            'type' => 0,
            'hide' => 0,
            'permission' => "sys:{$moduleName}:view",
        ];
        $result = $menuModel->edit($data);
        if ($result) {
            // 去除表描述中的`管理`
            if (strpos($moduleTitle, "管理") !== false) {
                $moduleTitle = str_replace("管理", null, $moduleTitle);
            }
            // 删除以存在的节点
            $menuModel = new MenuModel();
            $funcIds = $menuModel->getColumn([
                ['pid', "=", $result],
                ['type', "=", 1],
            ]);
            $menuModel->deleteAll($funcIds, true);

            // 创建节点
            $funcList = [1, 5, 10, 15, 25, 30];
            foreach ($funcList as $val) {
                $item = [];
                $item['pid'] = $result;
                $item['type'] = 1;
                $item['status'] = 1;
                $item['sort'] = intval($val);
                $item['target'] = "_self";
                if ($val == 1) {
                    // 查询
                    $item['title'] = "查询" . $moduleTitle;
                    $item['path'] = "/{$moduleName}/index";
                    $item['permission'] = "sys:{$moduleName}:index";
                } else if ($val == 5) {
                    // 添加
                    $item['title'] = "添加" . $moduleTitle;
                    $item['path'] = "/{$moduleName}/edit";
                    $item['permission'] = "sys:{$moduleName}:add";
                } else if ($val == 10) {
                    // 修改
                    $item['title'] = "修改" . $moduleTitle;
                    $item['path'] = "/{$moduleName}/edit";
                    $item['permission'] = "sys:{$moduleName}:edit";
                } else if ($val == 15) {
                    // 删除
                    $item['title'] = "删除" . $moduleTitle;
                    $item['path'] = "/{$moduleName}/delete";
                    $item['permission'] = "sys:{$moduleName}:delete";
                } else if ($val == 20) {
                    // 详情
                    $item['title'] = $moduleTitle . "详情";
                    $item['path'] = "/{$moduleName}/detail";
                    $item['permission'] = "sys:{$moduleName}:detail";
                } else if ($val == 25) {
                    // 状态
                    $item['title'] = "设置状态";
                    $item['path'] = "/{$moduleName}/status";
                    $item['permission'] = "sys:{$moduleName}:status";
                } else if ($val == 30) {
                    // 批量删除
                    $item['title'] = "批量删除";
                    $item['path'] = "/{$moduleName}/dall";
                    $item['permission'] = "sys:{$moduleName}:dall";
                }
                if (empty($item['title'])) {
                    continue;
                }
                $menuModel = new MenuModel();
                $menuModel->edit($item);
            }
        }
    }

    /**
     * 生成理由
     * @param $author 作者
     * @param $moduleName 模块名
     * @param $moduleTitle 模块标题
     * @param $moduleTitle 模块标题
     * @author 牧羊人
     * @since 2021/10/23
     */
    public function generateRoute($author, $moduleName, $moduleTitle, $tableName)
    {
        // 获取数据列表
        $columnList = $this->getColumnList(DB_PREFIX . "{$tableName}");
        // 参数
        $param = [
            'author' => $author,
            'since' => date('Y/m/d', time()),
            'moduleName' => $moduleName,
            'moduleTitle' => $moduleTitle,
            'columnList' => $columnList,
        ];
        // 存储目录
        $FILE_PATH = base_path() . '/routes/web';
        if (!is_dir($FILE_PATH)) {
            // 创建目录并赋予权限
            mkdir($FILE_PATH, 0777, true);
        }
        // 文件名
        $filename = $FILE_PATH . "/" . lcfirst($moduleName) . ".php";
        // 拆解参数
        extract($param);
        // 开启缓冲区
        ob_start();
        // 引入模板文件
        require(resource_path() . '/views/templates/route.blade.php');
        // 获取缓冲区内容
        $out = ob_get_clean();
        // 打开文件
        $f = fopen($filename, 'w');
        // 写入内容
        fwrite($f, "<?php " . $out);
        // 关闭
        fclose($f);
    }

}
