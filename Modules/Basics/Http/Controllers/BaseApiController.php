<?php
/**
 * @Name 当前模块控制器基类
 * @Description
 * @Auther Winston
 * @Date 2021/6/11 16:57
 */

namespace Modules\Basics\Http\Controllers;


use Modules\Common\Controllers\BaseController;

class BaseApiController extends BaseController
{
    public function __construct(){
        parent::__construct();
    }
}
