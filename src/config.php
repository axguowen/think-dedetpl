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

return [
    // 模板路径
    'view_path'             => '',
    // 模板文件后缀
    'view_suffix'           => 'htm',
    // 模板文件分隔符
    'view_depr'             => DIRECTORY_SEPARATOR,
    // 模板引擎禁用函数列表
    'deny_func_list'        => ['echo', 'exit'],
    // 默认模板引擎是否禁用PHP原生代码
    'deny_runphp'           => false,
    // 标签命名空间
    'tag_namespace'         => 'itzjj',
    // 标签开始标记
    'tag_begin'             => '{',
    // 标签结束标记
    'tag_end'               => '}',
    // 是否去除模板文件里面的html空格与换行
    'strip_space'           => false,
    // 标签名称最大长度
    'tag_maxlen'            => 64,
    // 属性源码最大长度
    'attr_maxlen'           => 1024,
    // 标签名称和属性不区分大小写
    'insensitive'           => true,
    // 模板字符串过滤
    'replace_string'        => [],
    // 标签库列表
    'taglibs' => [

    ],
];
