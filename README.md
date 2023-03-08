# think-dedetpl

ThinkPHP织梦模板引擎

主要功能：

支持织梦标签渲染页面

## 安装

~~~php
composer require axguowen/think-dedetpl
~~~

## 用法示例

本扩展不能单独使用，依赖ThinkPHP6.0+

首先配置config目录下的dedetpl.php配置文件，然后可以按照下面的用法使用。

简单使用

~~~php

// 实例化引擎
$dedeTemplate = new \axguowen\Dedetpl();
// 模板路径
$templatePath = $this->app->getRootPath() . '/public/views/default.htm';
// 生成静态
$dedeTemplate->fetch($templatePath, ['title' => '标题', 'name' => '测试']);

~~~

## 配置说明

~~~php

// 短信配置
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

~~~