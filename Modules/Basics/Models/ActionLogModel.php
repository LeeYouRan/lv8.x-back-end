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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * 行为日志-模型
 * @author 牧羊人
 * @since 2020/11/10
 * Class ActionLogModel
 * @package App\Models
 */
class ActionLogModel extends BaseModel
{
    // 设置数据表
    protected $table = null;
    // 自定义日志标题
    protected static $title = '';
    // 自定义日志内容
    protected static $content = '';
    // 自定义用户名
    protected static $username = '';

    public function __construct()
    {
        // 设置表名
        $this->table = 'action_log_' . date('Y') . '_' . date('m');
        // 初始化行为日志表
        $this->initTable();
    }

    /**
     * 初始化行为日志表
     * @return string|null
     * @since 2020/11/10
     * @author 牧羊人
     */
    private function initTable()
    {
        $tbl = DB_PREFIX . $this->table;
        if (!$this->tableExists($tbl)) {
            $sql = "CREATE TABLE `{$tbl}` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '唯一性标识',
                  `username` varchar(60) CHARACTER SET utf8mb4 NOT NULL COMMENT '操作人用户名',
                  `method` varchar(20) CHARACTER SET utf8mb4 NOT NULL COMMENT '请求类型',
                  `module` varchar(30) NOT NULL COMMENT '模型',
                  `action` varchar(255) NOT NULL COMMENT '操作方法',
                  `url` varchar(200) CHARACTER SET utf8mb4 NOT NULL COMMENT '操作页面',
                  `param` text CHARACTER SET utf8mb4 NOT NULL COMMENT '请求参数(JSON格式)',
                  `title` varchar(100) NOT NULL COMMENT '日志标题',
                  `type` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '操作类型：0操作日志 1登录系统 2注销系统',
                  `content` varchar(1000) NOT NULL DEFAULT '' COMMENT '内容',
                  `ip` varchar(18) CHARACTER SET utf8mb4 NOT NULL COMMENT 'IP地址',
                  `user_agent` varchar(360) CHARACTER SET utf8mb4 NOT NULL COMMENT 'User-Agent',
                  `create_user` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加人',
                  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
                  `update_user` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新人',
                  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
                  `mark` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '有效标识：1正常 0删除',
                  PRIMARY KEY (`id`) USING BTREE
                ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='系统行为日志表';";
            DB::select($sql);
        }
        return $tbl;
    }

    /**
     * 设置标题
     * @param $title 标题
     * @since 2020/11/10
     * @author 牧羊人
     */
    public static function setTitle($title)
    {
        self::$title = $title;
    }

    /**
     * 设置内容
     * @param $content 内容
     * @since 2020/11/10
     * @author 牧羊人
     */
    public static function setContent($content)
    {
        self::$content = $content;
    }

    /**
     * 设置用户名
     * @param $username
     * @since 2021/7/8
     * @author 牧羊人
     */
    public static function setUsername($username)
    {
        self::$username = $username;
    }

    /**
     * 创建行为日志
     * @author 牧羊人
     * @since 2020/11/10
     */
    public static function record()
    {
        if (!self::$title) {
            // 参数
            $param = request()->all();
            // 获取操作节点
            $path = request()->path();
            $item = explode("/", $path);
            if (count($item) == 2) {
                if ($item[1] == "edit" && !isset($param['id'])) {
                    $item[1] = "add";
                }
                $menuModel = new MenuModel();
                $info = $menuModel->getOne([
                    ['permission', 'like', "sys:{$item[0]}:{$item[1]}"],
                ]);
                self::$title = isset($info['title']) ? $info['title'] : "";
            }
        }
        if (self::$title) {
            // 登录用户ID
            $userId = JwtUtils::getUserId();
            $userModel = new UserModel();
            $userInfo = $userModel->getInfo($userId);

            // 日志数据
            $data = [
                'username' => isset($userInfo['username']) ? $userInfo['username'] : '未知',
                'module' => 'admin',
                'action' => request()->path(),
                'method' => request()->method(),
                'url' => request()->url(), // 获取完成URL
                'param' => request()->all() ? json_encode(request()->all()) : '',
                'title' => self::$title ? self::$title : '未知',
                'type' => self::$title == '登录系统' ? 1 : (self::$title == '注销系统' ? 2 : 0),
                'content' => self::$content,
                'ip' => request()->ip(),
                'user_agent' => request()->server('HTTP_USER_AGENT'),
                'create_user' => $userId,
                'create_time' => time(),
            ];
            // 日志入库
            self::insert($data);
        }
    }
}
