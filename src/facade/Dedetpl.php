<?php
// +----------------------------------------------------------------------
// | ThinkDedetpl [Dedetpl package for ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP织梦模板引擎
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

namespace think\facade;

use think\Facade;

/**
 * @see \think\Dedetpl
 * @mixin \think\Dedetpl
 */
class Dedetpl extends Facade
{
    protected static $alwaysNewInstance = true;

    /**
     * 获取当前Facade对应类名
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return \think\Dedetpl::class;
    }
}
