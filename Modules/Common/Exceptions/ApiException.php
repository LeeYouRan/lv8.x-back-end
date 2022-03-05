<?php
// +----------------------------------------------------------------------
// | Name: 管理系统 [ 为了快速搭建软件应用而生的，希望能够帮助到大家提高开发效率。 ]
// +----------------------------------------------------------------------
// | Copyright: (c) 2021~2022 https://www.liyouran.top All rights reserved.
// +----------------------------------------------------------------------
// | Licensed: 这是一个自由软件，允许对程序代码进行修改，但希望您留下原有的注释。
// +----------------------------------------------------------------------
// | Author: Winston <liyouran@live.com>
// +----------------------------------------------------------------------
// | Version: V1
// +----------------------------------------------------------------------

/**
 * @Name
 * @Description
 * @Auther Winston
 * @Date 2021/12/26 08:26
 */

namespace Modules\Common\Exceptions;

use Throwable;

class ApiException extends \Exception
{


    public function __construct(array $apiErrConst, Throwable $previous = null)
    {
        parent::__construct($apiErrConst['message'],$apiErrConst['status'], $previous);
    }
}
