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

namespace axguowen\dedetpl;

abstract class TagLib
{
    /**
     * 标签变量
     * @var array
     */
    protected $data = [];

    /**
     * 模板解析配置参数
     * @var array
     */
    protected $config = [
        
    ];

    /**
     * 当前标签对象
     * @var object
     */
    protected $tag;

    /**
     * 架构函数
     * @access public
     * @param \axguowen\dedetpl\DedeTag $tag 标签对象
     * @param array $data 标签变量
     */
    public function __construct($tag = null, $data = [])
    {
        // 设置标签对象
        $this->tag = $tag;
        // 设置标签变量
        $this->data = $data;
    }

    /**
     * 设置标签配置
     * @access public
     * @param array $config 配置参数
     * @return $this
     */
    public function config($config)
    {
        // 合并配置参数
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * 获取标签配置项
     * @access public
     * @param string $name 配置项
     * @return mixed
     */
    public function getConfig($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    /**
     * 标签变量获取
     * @access public
     * @param string $name 变量名
     * @return mixed
     */
    public function getData($name = '')
    {
        // 如果为空
        if(empty($name)){
            return '';
        }
        // 如果为获取全部数组
        if ($name == 'array') {
            return $this->data;
        }
        $data = $this->data;
        // 获取变量
        foreach (explode('.', $name) as $key => $val) {
            if (isset($data[$val])) {
                $data = $data[$val];
            } else {
                $data = null;
                break;
            }
        }
        // 返回
        return $data;
    }

    /**
     * 设置标签变量
     * @access public
     * @param array $data 标签变量
     * @return $this
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 解析$this.语法的值
     * @access protected
     * @param string $text 值
     * @return string
     */
    protected function parseThisValue($text = '')
    {
        // 使用正则匹配并替换
        $textParsed = preg_replace_callback( '/\$this\.\w+(\.\w+)*/', function($matches){
            // 获取匹配的变量名
            $name = str_replace('$this.', '', $matches[0]);
            // 从当前数据中获取变量值
            $value = $this->getData($name);
            // 返回替换的内容
            return $value;
        }, $text);
        // 返回替换后的结果
        return $textParsed;
    }

    /**
     * 获取标签的指定属性
     * @access protected
     * @param string $attrName 属性名
     * @param mixed $default 默认值
     * @param string $filter 过滤规则
     * @return string
     */
    protected function getAttr($attrName, $default = null, $filter = '')
    {
        // 为空
        if(empty($attrName)){
            return '';
        }
        // 转换为字符串
        $attrName = (string) $attrName;
        // 解析指定的属性名
        if (strpos($attrName, '/')) {
            list($attrName, $type) = explode('/', $attrName);
        }
        // 通过当前标签实例获取标签属性
        $attrValue = $this->tag->getAttr($attrName);
        // 如果不存在则返回默认值
        if(is_null($attrValue)){
            return $default;
        }
        // 解析$this.语法
        $value = $this->parseThisValue($attrValue);
        
        // 如果存在过滤器
        if(!empty($filter)){
            // 是函数
            if(preg_match('/^\/\S*\/$/',$filter)){
                $value = preg_replace($filter, '', $value);
            }
            else if(is_callable($filter)){
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            }
            else{
                // 默认值
                $value = $default;
            }
        }
        
        // 如果指定类型
        if (isset($type) && $value !== $default) {
            // 强制类型转换
            $value = \axguowen\dedetpl\helper\Str::typeCast($value, $type);
        }
        // 返回替换后的结果
        return $value;
    }

    /**
     * 获取标签内容体
     * @access protected
     * @return string
     */
    protected function getInnerText()
    {
        return $this->tag->getInnerText();
    }

    /**
     * 渲染标签内容体并取得解析结果
     * @access public
     * @param array $data 标签变量
     * @return string 成功后返回解析后的标签内容
     */
    abstract function fetch($data = []);
}
