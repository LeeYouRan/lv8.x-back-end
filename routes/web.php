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

use App\Http\Controllers\ActionLogController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\AdSortController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ConfigDataController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ConfigWebController;
use App\Http\Controllers\Demo3Controller;
use App\Http\Controllers\DeptController;
use App\Http\Controllers\DictDataController;
use App\Http\Controllers\DictController;
use App\Http\Controllers\ExampleController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\ItemCateController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LayoutController;
use App\Http\Controllers\LayoutDescController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberLevelController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 系统登录
Route::get('/captcha', [LoginController::class, 'captcha'])->name('captcha');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout']);

Route::group(['middleware' => ['user.login']], function () {

    // 文件上传
    Route::prefix('upload')->group(function () {
        Route::post('uploadImage', [UploadController::class, 'uploadImage']);
        Route::post('uploadFile', [UploadController::class, 'uploadFile']);
    });

    // 系统主页
    Route::prefix('index')->group(function () {
        Route::get('getMenuList', [IndexController::class, 'getMenuList']);
        Route::get('getUserInfo', [IndexController::class, 'getUserInfo']);
        Route::post('updateUserInfo', [IndexController::class, 'updateUserInfo']);
        Route::post('updatePwd', [IndexController::class, 'updatePwd']);
    });

    // 用户管理
    Route::prefix('user')->group(function () {
        Route::get('index', [UserController::class, 'index']);
        Route::get('info', [UserController::class, 'info']);
        Route::post('edit', [UserController::class, 'edit']);
        Route::post('delete', [UserController::class, 'delete']);
        Route::post('status', [UserController::class, 'status']);
        Route::post('resetPwd', [UserController::class, 'resetPwd']);
    });

    // 职级管理
    Route::prefix('level')->group(function () {
        Route::get('index', [LevelController::class, 'index']);
        Route::get('info', [LevelController::class, 'info']);
        Route::post('edit', [LevelController::class, 'edit']);
        Route::post('delete', [LevelController::class, 'delete']);
        Route::post('status', [LevelController::class, 'status']);
        Route::get('getLevelList', [LevelController::class, 'getLevelList']);
        Route::post('importExcel', [LevelController::class, 'importExcel']);
        Route::get('exportExcel', [LevelController::class, 'exportExcel']);
    });

    // 岗位管理
    Route::prefix('position')->group(function () {
        Route::get('index', [PositionController::class, 'index']);
        Route::get('info', [PositionController::class, 'info']);
        Route::post('edit', [PositionController::class, 'edit']);
        Route::post('delete', [PositionController::class, 'delete']);
        Route::post('status', [PositionController::class, 'status']);
        Route::get('getPositionList', [PositionController::class, 'getPositionList']);
    });

    // 角色管理
    Route::prefix('role')->group(function () {
        Route::get('index', [RoleController::class, 'index']);
        Route::get('info', [RoleController::class, 'info']);
        Route::post('edit', [RoleController::class, 'edit']);
        Route::post('delete', [RoleController::class, 'delete']);
        Route::post('status', [RoleController::class, 'status']);
        Route::get('getRoleList', [RoleController::class, 'getRoleList']);
        Route::get('getPermissionList', [RoleController::class, 'getPermissionList']);
        Route::post('savePermission', [RoleController::class, 'savePermission']);
    });

    // 菜单管理
    Route::prefix('menu')->group(function () {
        Route::get('index', [MenuController::class, 'index']);
        Route::get('info', [MenuController::class, 'info']);
        Route::post('edit', [MenuController::class, 'edit']);
        Route::post('delete', [MenuController::class, 'delete']);
        Route::get('getMenuAll', [MenuController::class, 'getMenuAll']);
    });

    // 部门管理
    Route::prefix('dept')->group(function () {
        Route::get('index', [DeptController::class, 'index']);
        Route::get('info', [DeptController::class, 'info']);
        Route::post('edit', [DeptController::class, 'edit']);
        Route::post('delete', [DeptController::class, 'delete']);
        Route::get('getDeptList', [DeptController::class, 'getDeptList']);
    });

    // 城市管理
    Route::prefix('city')->group(function () {
        Route::get('index', [CityController::class, 'index']);
        Route::get('info', [CityController::class, 'info']);
        Route::post('edit', [CityController::class, 'edit']);
        Route::post('delete', [CityController::class, 'delete']);
    });

    // 字典管理
    Route::prefix('dict')->group(function () {
        Route::get('index', [DictController::class, 'index']);
        Route::get('info', [DictController::class, 'info']);
        Route::post('edit', [DictController::class, 'edit']);
        Route::post('delete', [DictController::class, 'delete']);
    });

    // 字典数据管理
    Route::prefix('dictdata')->group(function () {
        Route::get('index', [DictDataController::class, 'index']);
        Route::get('info', [DictDataController::class, 'info']);
        Route::post('edit', [DictDataController::class, 'edit']);
        Route::post('delete', [DictDataController::class, 'delete']);
        Route::post('status', [DictDataController::class, 'status']);
        Route::get('getDictByCode', [DictDataController::class, 'getDictByCode']);
    });

    // 配置管理
    Route::prefix('config')->group(function () {
        Route::get('index', [ConfigController::class, 'index']);
        Route::get('info', [ConfigController::class, 'info']);
        Route::post('edit', [ConfigController::class, 'edit']);
        Route::post('delete', [ConfigController::class, 'delete']);
    });

    // 配置项管理
    Route::prefix('configdata')->group(function () {
        Route::get('index', [ConfigDataController::class, 'index']);
        Route::get('info', [ConfigDataController::class, 'info']);
        Route::post('edit', [ConfigDataController::class, 'edit']);
        Route::post('delete', [ConfigDataController::class, 'delete']);
        Route::post('status', [ConfigDataController::class, 'status']);
    });

    // 通知公告
    Route::prefix('notice')->group(function () {
        Route::get('index', [NoticeController::class, 'index']);
        Route::get('info', [NoticeController::class, 'info']);
        Route::post('edit', [NoticeController::class, 'edit']);
        Route::post('delete', [NoticeController::class, 'delete']);
        Route::post('status', [NoticeController::class, 'status']);
        Route::post('setIsTop', [NoticeController::class, 'setIsTop']);
    });

    // 站点管理
    Route::prefix('item')->group(function () {
        Route::get('index', [ItemController::class, 'index']);
        Route::get('info', [ItemController::class, 'info']);
        Route::post('edit', [ItemController::class, 'edit']);
        Route::post('delete', [ItemController::class, 'delete']);
        Route::post('status', [ItemController::class, 'status']);
        Route::get('getItemList', [ItemController::class, 'getItemList']);
    });

    // 栏目管理
    Route::prefix('itemcate')->group(function () {
        Route::get('index', [ItemCateController::class, 'index']);
        Route::get('info', [ItemCateController::class, 'info']);
        Route::post('edit', [ItemCateController::class, 'edit']);
        Route::post('delete', [ItemCateController::class, 'delete']);
        Route::post('status', [ItemCateController::class, 'status']);
        Route::get('getCateList', [ItemCateController::class, 'getCateList']);
    });

    // 广告位管理
    Route::prefix('adsort')->group(function () {
        Route::get('index', [AdSortController::class, 'index']);
        Route::get('info', [AdSortController::class, 'info']);
        Route::post('edit', [AdSortController::class, 'edit']);
        Route::post('delete', [AdSortController::class, 'delete']);
        Route::get('getAdSortList', [AdSortController::class, 'getAdSortList']);
    });

    // 广告管理
    Route::prefix('ad')->group(function () {
        Route::get('index', [AdController::class, 'index']);
        Route::get('info', [AdController::class, 'info']);
        Route::post('edit', [AdController::class, 'edit']);
        Route::post('delete', [AdController::class, 'delete']);
        Route::post('status', [AdController::class, 'status']);
    });

    // 布局描述
    Route::prefix('layoutdesc')->group(function () {
        Route::get('index', [LayoutDescController::class, 'index']);
        Route::get('info', [LayoutDescController::class, 'info']);
        Route::post('edit', [LayoutDescController::class, 'edit']);
        Route::post('delete', [LayoutDescController::class, 'delete']);
        Route::get('getLayoutDescList', [LayoutDescController::class, 'getLayoutDescList']);
    });

    // 布局管理
    Route::prefix('layout')->group(function () {
        Route::get('index', [LayoutController::class, 'index']);
        Route::get('info', [LayoutController::class, 'info']);
        Route::post('edit', [LayoutController::class, 'edit']);
        Route::post('delete', [LayoutController::class, 'delete']);
    });

    // 友链管理
    Route::prefix('link')->group(function () {
        Route::get('index', [LinkController::class, 'index']);
        Route::get('info', [LinkController::class, 'info']);
        Route::post('edit', [LinkController::class, 'edit']);
        Route::post('delete', [LinkController::class, 'delete']);
        Route::post('status', [LinkController::class, 'status']);
    });

    // 文章管理
    Route::prefix('article')->group(function () {
        Route::get('index', [ArticleController::class, 'index']);
        Route::get('info', [ArticleController::class, 'info']);
        Route::post('edit', [ArticleController::class, 'edit']);
        Route::post('delete', [ArticleController::class, 'delete']);
        Route::post('status', [ArticleController::class, 'status']);
    });

    // 会员等级
    Route::prefix('memberlevel')->group(function () {
        Route::get('index', [MemberLevelController::class, 'index']);
        Route::get('info', [MemberLevelController::class, 'info']);
        Route::post('edit', [MemberLevelController::class, 'edit']);
        Route::post('delete', [MemberLevelController::class, 'delete']);
        Route::get('getMemberLevelList', [MemberLevelController::class, 'getMemberLevelList']);
    });

    // 会员管理
    Route::prefix('member')->group(function () {
        Route::get('index', [MemberController::class, 'index']);
        Route::get('info', [MemberController::class, 'info']);
        Route::post('edit', [MemberController::class, 'edit']);
        Route::post('delete', [MemberController::class, 'delete']);
        Route::post('status', [MemberController::class, 'status']);
    });

    // 登录日志
    Route::prefix('loginlog')->group(function () {
        Route::get('index', [LoginLogController::class, 'index']);
        Route::post('delete', [LoginLogController::class, 'delete']);
    });

    // 操作日志
    Route::prefix('actionlog')->group(function () {
        Route::get('index', [ActionLogController::class, 'index']);
        Route::post('delete', [ActionLogController::class, 'delete']);
    });

    // 代码生成器
    Route::prefix('generate')->group(function () {
        Route::get('index', [GenerateController::class, 'index']);
        Route::post('generate', [GenerateController::class, 'generate']);
        Route::post('batchGenerate', [GenerateController::class, 'batchGenerate']);
    });

    // 网站配置
    Route::prefix('configweb')->group(function () {
        Route::get('index', [ConfigWebController::class, 'index']);
        Route::post('edit', [ConfigWebController::class, 'edit']);
    });

    // 组织机构
    Route::prefix('organization')->group(function () {
        Route::get('index', [OrganizationController::class, 'index']);
        Route::get('info', [OrganizationController::class, 'info']);
        Route::post('edit', [OrganizationController::class, 'edit']);
        Route::post('delete', [OrganizationController::class, 'delete']);
    });

    // 脚本数据
    Route::prefix('test')->group(function () {
        Route::get('index', [TestController::class, 'index']);
    });

});
